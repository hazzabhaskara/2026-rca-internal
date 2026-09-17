"""
Phase B + C: ABN Batch Lookup & Data Enrichment
Scope: Aged Care companies (278) + Company Specific (296)
"""

import sys
import os
sys.path.insert(0, r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\scripts\python')

import pandas as pd
from la_abn_lookup import batch_lookup

# ============================================================
# LOAD CLASSIFIED DATA
# ============================================================
print("="*60)
print("PHASE B: ABN Batch Lookup for State/Region Enrichment")
print("="*60)

df = pd.read_csv(r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\la_classified_temp.csv')
print(f"Loaded {len(df)} records")

# ============================================================
# IDENTIFY COMPANIES TO LOOKUP (Opsi B + C from plan)
# Aged Care related (278) + Company Specific (296)
# ============================================================
mask_aged_care = df['is_aged_care_related'] == True
mask_company_specific = df['la_category'] == 'Company Specific'
mask_no_state = (df['state_region'] == '') | df['state_region'].isna()

# Combine: aged care OR company specific, AND missing state
target_mask = (mask_aged_care | mask_company_specific) & mask_no_state
target_df = df[target_mask].copy()

print(f"\nTarget companies for ABN lookup:")
print(f"  Aged Care related (missing state): {(mask_aged_care & mask_no_state).sum()}")
print(f"  Company Specific (missing state): {(mask_company_specific & mask_no_state).sum()}")
print(f"  Total unique to lookup: {len(target_df)}")
print(f"  (DAMA companies already have region - skipped)")

# Get unique company names for lookup
companies_to_lookup = target_df['companyname'].unique().tolist()
print(f"\n  Unique company names: {len(companies_to_lookup)}")

# ============================================================
# RUN BATCH ABN LOOKUP
# ============================================================
print(f"\nStarting ABN Lookup (delay=0.8s per company)...")
print(f"Estimated time: {len(companies_to_lookup) * 0.8 / 60:.1f} minutes")
print("(Cached results will be instant)\n")

abn_results = batch_lookup(companies_to_lookup, delay=0.8, verbose=True)

# ============================================================
# MERGE RESULTS BACK TO DATAFRAME
# ============================================================
print("\nMerging ABN data back to main dataframe...")

def get_state_from_abn(company_name, abn_results):
    """Get state string - multiple states separated by comma."""
    res = abn_results.get(company_name, {})
    states = res.get('states', [])
    if states:
        return ', '.join(states)
    return ''

def get_postcodes_from_abn(company_name, abn_results):
    """Get postcode string."""
    res = abn_results.get(company_name, {})
    postcodes = res.get('postcodes', [])
    return ', '.join(postcodes) if postcodes else ''

# Add new columns
df['registered_state'] = df.apply(
    lambda row: get_state_from_abn(row['companyname'], abn_results)
    if ((row['is_aged_care_related'] == True or row['la_category'] == 'Company Specific')
        and ((row['state_region'] == '') or pd.isna(row['state_region'])))
    else '',
    axis=1
)

df['registered_postcodes'] = df.apply(
    lambda row: get_postcodes_from_abn(row['companyname'], abn_results)
    if ((row['is_aged_care_related'] == True or row['la_category'] == 'Company Specific')
        and ((row['state_region'] == '') or pd.isna(row['state_region'])))
    else '',
    axis=1
)

# ============================================================
# ADD LA COVERAGE COLUMN
# ============================================================
def get_la_coverage(row):
    latype = row['latype']
    if 'DAMA' in latype:
        return row.get('state_region', '')
    elif 'Industry' in latype:
        # Industry = National agreement
        return 'National'
    elif 'Company Specific' in latype:
        return 'Company Specific (varies)'
    elif 'Skilled Refugee' in latype:
        return 'National'
    elif 'GTS' in latype:
        return 'National'
    return 'Unknown'

df['la_coverage'] = df.apply(get_la_coverage, axis=1)

# ============================================================
# FILL state_region with best available data
# ============================================================
def get_best_state_region(row):
    # DAMA already has region
    if row.get('state_region', '') not in ('', None) and not pd.isna(row.get('state_region', '')):
        return row['state_region']
    # Use registered_state from ABN
    if row.get('registered_state', ''):
        return row['registered_state']
    # Fallback to LA coverage
    if 'Industry' in str(row.get('latype', '')):
        return 'National'
    return 'Unknown'

df['state_region_final'] = df.apply(get_best_state_region, axis=1)

# ============================================================
# STATISTICS
# ============================================================
print("\n" + "="*60)
print("ENRICHMENT RESULTS:")
print("="*60)

# How many got resolved?
resolved = df[
    (mask_aged_care | mask_company_specific) &
    mask_no_state &
    (df['registered_state'] != '')
].shape[0]

unresolved = df[
    (mask_aged_care | mask_company_specific) &
    mask_no_state &
    (df['registered_state'] == '')
].shape[0]

print(f"Successfully resolved: {resolved}/{len(target_df)} companies")
print(f"Could not resolve: {unresolved} companies")

# Multi-state companies
multi_state = df[df['registered_state'].str.contains(',', na=False)]
print(f"\nCompanies operating in MULTIPLE states: {len(multi_state)}")
if len(multi_state) > 0:
    print("Top multi-state companies:")
    for _, row in multi_state.head(10).iterrows():
        print(f"  {row['companyname']} | {row['registered_state']}")

print("\nFinal state_region_final breakdown (top 15):")
print(df['state_region_final'].value_counts().head(15).to_string())

# ============================================================
# SAVE ENRICHED DATA
# ============================================================
df.to_csv(r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\la_enriched_temp.csv', index=False)
print(f"\nEnriched data saved to la_enriched_temp.csv")
print("Phase B+C complete. Ready for Excel generation.")
