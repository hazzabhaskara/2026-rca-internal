"""
Phase 2: Aged Care Occupation List
Compile occupation list dari:
1. Industry - Aged Care Labour Agreement (immi.homeaffairs.gov.au)
2. DAMA regions yang berpotensi include aged care occupations
"""

import requests
import pandas as pd
import json

# ============================================================
# STEP 1: KNOWN AGED CARE OCCUPATIONS (ANZSCO)
# From: Aged Care Industry Labour Agreement (ACILA)
# Source: immi.homeaffairs.gov.au official docs
# ============================================================
print("Compiling Aged Care Occupation List from ACILA...")

acila_occupations = [
    {
        'source': 'Industry - Aged Care LA (ACILA)',
        'anzsco_code': '411411',
        'occupation_title': 'Enrolled Nurse',
        'skill_level': 'Skill Level 2',
        'tsmit_concession': 'No',
        'english_concession': 'Yes - IELTS 5.0 (or equivalent)',
        'age_concession': 'Up to 55 years',
        'notes': 'Must hold relevant qualification & registration with AHPRA'
    },
    {
        'source': 'Industry - Aged Care LA (ACILA)',
        'anzsco_code': '423111',
        'occupation_title': 'Aged or Disabled Carer',
        'skill_level': 'Skill Level 3',
        'tsmit_concession': 'Yes - below TSMIT threshold allowed',
        'english_concession': 'Yes - IELTS 5.0 (or equivalent)',
        'age_concession': 'Up to 55 years',
        'notes': 'Cert III in Individual Support required. High demand occupation.'
    },
    {
        'source': 'Industry - Aged Care LA (ACILA)',
        'anzsco_code': '423312',
        'occupation_title': 'Nursing Support Worker',
        'skill_level': 'Skill Level 3',
        'tsmit_concession': 'Yes - below TSMIT threshold allowed',
        'english_concession': 'Yes - IELTS 5.0 (or equivalent)',
        'age_concession': 'Up to 55 years',
        'notes': 'Works under supervision of nurses. Relevant certification required.'
    },
    {
        'source': 'Industry - Aged Care LA (ACILA)',
        'anzsco_code': '423313',
        'occupation_title': 'Personal Care Assistant',
        'skill_level': 'Skill Level 3',
        'tsmit_concession': 'Yes - below TSMIT threshold allowed',
        'english_concession': 'Yes - IELTS 5.0 (or equivalent)',
        'age_concession': 'Up to 55 years',
        'notes': 'May work in residential or community aged care settings.'
    },
    {
        'source': 'Industry - Aged Care LA (ACILA)',
        'anzsco_code': '254411',
        'occupation_title': 'Registered Nurse (Aged Care)',
        'skill_level': 'Skill Level 1',
        'tsmit_concession': 'No - full TSMIT applies',
        'english_concession': 'Standard requirements',
        'age_concession': 'Standard requirements',
        'notes': 'Must hold Bachelor degree & AHPRA registration'
    },
    {
        'source': 'Industry - Aged Care LA (ACILA)',
        'anzsco_code': '254412',
        'occupation_title': 'Registered Nurse (Critical Care and Emergency)',
        'skill_level': 'Skill Level 1',
        'tsmit_concession': 'No - full TSMIT applies',
        'english_concession': 'Standard requirements',
        'age_concession': 'Standard requirements',
        'notes': 'Specialist nursing role in aged care or hospital settings'
    },
    {
        'source': 'Industry - Aged Care LA (ACILA)',
        'anzsco_code': '254418',
        'occupation_title': 'Registered Nurses nec',
        'skill_level': 'Skill Level 1',
        'tsmit_concession': 'No - full TSMIT applies',
        'english_concession': 'Standard requirements',
        'age_concession': 'Standard requirements',
        'notes': 'Other specialist registered nursing roles'
    },
]

# ============================================================
# STEP 2: DAMA REGIONS WITH AGED CARE OCCUPATIONS
# Based on published information from DAR websites
# ============================================================
print("Compiling DAMA regions with Aged Care occupations...")

dama_aged_care_occupations = [
    # Northern Territory DAMA
    {'dama_name': 'Northern Territory DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes - PTE 30 / IELTS 4.5', 'age_concession': 'Up to 55', 'notes': 'NT DAMA III (2025) - 325 occupations total'},
    {'dama_name': 'Northern Territory DAMA', 'anzsco_code': '411411', 'occupation_title': 'Enrolled Nurse', 'english_concession': 'Yes', 'age_concession': 'Up to 55', 'notes': 'NT DAMA III'},
    {'dama_name': 'Northern Territory DAMA', 'anzsco_code': '254411', 'occupation_title': 'Registered Nurse (Aged Care)', 'english_concession': 'Partial concession', 'age_concession': 'Up to 55', 'notes': 'NT DAMA III'},
    {'dama_name': 'Northern Territory DAMA', 'anzsco_code': '423313', 'occupation_title': 'Personal Care Assistant', 'english_concession': 'Yes - PTE 30 / IELTS 4.5', 'age_concession': 'Up to 55', 'notes': 'NT DAMA III'},
    # South Australia Regional DAMA
    {'dama_name': 'South Australia Regional DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes - IELTS 5.0', 'age_concession': 'Up to 50', 'notes': 'SA Regional DAMA - managed by migration.sa.gov.au'},
    {'dama_name': 'South Australia Regional DAMA', 'anzsco_code': '411411', 'occupation_title': 'Enrolled Nurse', 'english_concession': 'Yes', 'age_concession': 'Up to 50', 'notes': 'SA Regional DAMA'},
    {'dama_name': 'South Australia Regional DAMA', 'anzsco_code': '423312', 'occupation_title': 'Nursing Support Worker', 'english_concession': 'Yes - IELTS 5.0', 'age_concession': 'Up to 50', 'notes': 'SA Regional DAMA'},
    # Far North Queensland DAMA
    {'dama_name': 'Far North Queensland DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes - IELTS 5.0', 'age_concession': 'Up to 50', 'notes': 'FNQ DAMA - managed by RDAFNQ'},
    {'dama_name': 'Far North Queensland DAMA', 'anzsco_code': '411411', 'occupation_title': 'Enrolled Nurse', 'english_concession': 'Yes', 'age_concession': 'Up to 50', 'notes': 'FNQ DAMA'},
    # WA DAMA
    {'dama_name': 'WA DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes - IELTS 5.0', 'age_concession': 'Up to 50', 'notes': 'WA State DAMA - migration.wa.gov.au'},
    {'dama_name': 'WA DAMA', 'anzsco_code': '423312', 'occupation_title': 'Nursing Support Worker', 'english_concession': 'Yes', 'age_concession': 'Up to 50', 'notes': 'WA State DAMA'},
    # South West DAMA (WA)
    {'dama_name': 'South West DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes - IELTS 5.0', 'age_concession': 'Up to 45', 'notes': 'South West WA DAMA'},
    {'dama_name': 'South West DAMA', 'anzsco_code': '411411', 'occupation_title': 'Enrolled Nurse', 'english_concession': 'Partial', 'age_concession': 'Up to 45', 'notes': 'South West WA DAMA'},
    # Orana DAMA (NSW)
    {'dama_name': 'Orana DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes - IELTS 5.0', 'age_concession': 'Up to 45', 'notes': 'Orana NSW DAMA - rdaorana.org.au'},
    {'dama_name': 'Orana DAMA', 'anzsco_code': '423312', 'occupation_title': 'Nursing Support Worker', 'english_concession': 'Yes', 'age_concession': 'Up to 45', 'notes': 'Orana NSW DAMA'},
    # Townsville DAMA
    {'dama_name': 'Townsville DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes - IELTS 5.0', 'age_concession': 'Up to 50', 'notes': 'Townsville QLD DAMA'},
    {'dama_name': 'Townsville DAMA', 'anzsco_code': '411411', 'occupation_title': 'Enrolled Nurse', 'english_concession': 'Yes', 'age_concession': 'Up to 50', 'notes': 'Townsville QLD DAMA'},
    # Great South Coast DAMA (VIC)
    {'dama_name': 'Great South Coast DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes - IELTS 5.0', 'age_concession': 'Up to 45', 'notes': 'Great South Coast VIC DAMA'},
    # Goldfields DAMA (WA)
    {'dama_name': 'Goldfields DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes', 'age_concession': 'Up to 45', 'notes': 'Goldfields WA DAMA'},
    # East Kimberley DAMA (WA)
    {'dama_name': 'East Kimberley DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Yes', 'age_concession': 'Up to 45', 'notes': 'East Kimberley WA DAMA - ckb.wa.gov.au'},
    # Goulburn Valley DAMA (VIC)
    {'dama_name': 'Goulburn Valley DAMA', 'anzsco_code': '423111', 'occupation_title': 'Aged or Disabled Carer', 'english_concession': 'Partial', 'age_concession': 'Up to 45', 'notes': 'Goulburn Valley VIC DAMA'},
]

acila_df = pd.DataFrame(acila_occupations)
dama_occ_df = pd.DataFrame(dama_aged_care_occupations)

print(f"  ACILA occupations: {len(acila_df)}")
print(f"  DAMA occupation entries: {len(dama_occ_df)}")
print(f"  DAMA regions with aged care: {dama_occ_df['dama_name'].nunique()}")

# Save to CSV for Phase 4
acila_df.to_csv(r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\acila_occupations_temp.csv', index=False)
dama_occ_df.to_csv(r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\dama_occupations_temp.csv', index=False)

print("\nPhase 2 complete. Occupation data ready.")
print("\nACILA Occupation List:")
print(acila_df[['anzsco_code', 'occupation_title', 'skill_level']].to_string(index=False))
print("\nDAMA Regions with Aged Care Occupations:")
print(dama_occ_df.groupby('dama_name')['occupation_title'].count().rename('# Occupations').to_string())
