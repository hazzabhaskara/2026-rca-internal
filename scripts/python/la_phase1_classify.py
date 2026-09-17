"""
Phase 1 + 3: Data Extraction & Company Classification
Fokus Opsi A: Industry - Aged Care LA + DAMA companies terkait aged care
"""

import requests
import pandas as pd
from datetime import datetime, date

# ============================================================
# STEP 1: FETCH DATA FROM API
# ============================================================
print("Fetching Labour Agreement data from API...")
url = 'https://immi.homeaffairs.gov.au/_layouts/15/api/Data.aspx/GetLabourAgreementData'
r = requests.post(url, json={'webUrl': '/employer-subsite', 'category': 'All'},
                  headers={'Content-Type': 'application/json'})
r.raise_for_status()
data = r.json()['d']['data']
df = pd.DataFrame(data)
print(f"  Total records fetched: {len(df)}")

# ============================================================
# STEP 2: PARSE DATES & STATUS
# ============================================================
def parse_date(date_str):
    try:
        return datetime.strptime(date_str, '%d-%b-%Y').date()
    except:
        return None

today = date.today()
df['start_date_parsed'] = df['startdate'].apply(parse_date)
df['end_date_parsed'] = df['enddate'].apply(parse_date)

def get_status(end_date):
    if end_date is None:
        return 'Unknown'
    days_remaining = (end_date - today).days
    if days_remaining < 0:
        return 'Expired'
    elif days_remaining <= 180:
        return 'Expiring Soon (<6mo)'
    else:
        return 'Active'

df['status'] = df['end_date_parsed'].apply(get_status)
print(f"\nStatus breakdown:")
print(df['status'].value_counts().to_string())

# ============================================================
# STEP 3: LA CATEGORY CLASSIFICATION
# ============================================================
def get_la_category(latype):
    if 'DAMA' in latype:
        return 'DAMA'
    elif 'Industry' in latype:
        return 'Industry'
    elif 'Company Specific' in latype:
        return 'Company Specific'
    elif 'Skilled Refugee' in latype:
        return 'Skilled Refugee Pilot'
    elif 'GTS' in latype:
        return 'Global Talent Scheme'
    else:
        return 'Other'

df['la_category'] = df['latype'].apply(get_la_category)

# ============================================================
# STEP 4: STATE/REGION FROM DAMA TYPE
# ============================================================
dama_region_map = {
    'Northern Territory DAMA': 'Northern Territory',
    'Far North Queensland DAMA': 'Queensland (Far North)',
    'South Australia Regional DAMA': 'South Australia',
    'WA DAMA': 'Western Australia',
    'South West DAMA': 'Western Australia (South West)',
    'Orana DAMA': 'New South Wales (Orana)',
    'Goldfields DAMA': 'Western Australia (Goldfields)',
    'East Kimberley DAMA': 'Western Australia (East Kimberley)',
    'Pilbara DAMA': 'Western Australia (Pilbara)',
    'Townsville DAMA': 'Queensland (Townsville)',
    'Great South Coast DAMA': 'Victoria (Great South Coast)',
    'Goulburn Valley DAMA': 'Victoria (Goulburn Valley)',
    'Adelaide City Tech and Innovation DAMA': 'South Australia (Adelaide City)',
}

df['state_region'] = df['latype'].map(dama_region_map).fillna('')

# ============================================================
# STEP 5: SECTOR CLASSIFICATION
# ============================================================
aged_care_keywords = [
    'aged care', 'aged person', 'nursing home', 'nursing agency',
    'care group', 'care services', 'care ltd', 'care pty', 'care inc',
    'community care', 'residential care', 'home care', 'aged living',
    'senior living', 'senior care', 'elder care', 'elderly',
    'allcare', 'lifecare', 'warrigal', 'hammondcare',
    'benevolent', 'heritage living', 'carinya', 'masonic', 'freemasons',
    'benevolent institution', 'aged persons', 'seniors'
]

child_care_keywords = [
    'child care', 'childcare', 'child centre', 'early learning',
    'daycare', 'day care', 'kindergarten', 'preschool',
    'playmates', 'scallywags', 'stepping stones', 'playschool'
]

health_keywords = [
    'medical', 'clinic', 'hospital', 'health', 'dental', 'nurse',
    'nursing', 'pharmacy', 'allied health', 'physiother', 'pathology',
    'disability support', 'ndis', 'community services'
]

hospitality_keywords = [
    'hotel', 'motel', 'resort', 'restaurant', 'cafe', 'bakery',
    'club', 'pub', 'bar', 'tavern', 'dining', 'catering',
    'lodge', 'accommodation', 'hospitality', 'tourism', 'coffee',
    'pizza', 'food', 'dumpling', 'plaza', 'kitchen'
]

agriculture_keywords = [
    'farm', 'farming', 'agriculture', 'horticulture', 'dairy',
    'livestock', 'pastoral', 'station', 'vineyard', 'winery',
    'grain', 'crop', 'harvest', 'agri', 'aquaculture', 'vegie', 'meats'
]

mining_keywords = [
    'mining', 'resources', 'drilling', 'goldfield', 'mine',
    'quarry', 'excavation', 'exploration', 'geo', 'rock',
    'petroleum', 'gas', 'oil', 'earthwork', 'earthmoving'
]

construction_keywords = [
    'construction', 'building', 'engineering', 'civil', 'plumbing',
    'electrical', 'infrastructure', 'development', 'contracting',
    'roofing', 'concrete', 'tiling', 'fencing', 'landscaping'
]

fishing_keywords = [
    'fishing', 'fisheries', 'seafood', 'prawn', 'trawl', 'aquatic'
]

transport_keywords = [
    'transport', 'logistics', 'freight', 'haulage', 'trucking',
    'removals', 'courier', 'delivery', 'shipping'
]

def classify_sector(row):
    name = row['companyname'].lower()
    latype = row['latype'].lower()

    # Industry LA types take priority
    if 'industry - aged care' in latype:
        return 'Aged Care'
    if 'industry - dairy' in latype:
        return 'Agriculture - Dairy'
    if 'industry - horticulture' in latype:
        return 'Agriculture - Horticulture'
    if 'industry - meat' in latype:
        return 'Agriculture - Meat'
    if 'industry - pork' in latype:
        return 'Agriculture - Pork'
    if 'industry - fishing' in latype:
        return 'Fishing'
    if 'industry - minister of religion' in latype:
        return 'Religious / Ministry'
    if 'industry - on-hire' in latype:
        return 'On-Hire / Labour Hire'
    if 'industry - premium dining' in latype or 'premium dining' in latype:
        return 'Hospitality - Premium Dining'
    if 'industry - advertising' in latype:
        return 'Advertising / Media'
    if 'skilled refugee' in latype:
        return 'Skilled Refugee Pilot'
    if 'gts' in latype:
        return 'Global Talent Scheme'

    # Name-based classification
    if any(k in name for k in aged_care_keywords):
        return 'Aged Care'
    if any(k in name for k in child_care_keywords):
        return 'Child Care'
    if any(k in name for k in health_keywords):
        return 'Healthcare'
    if any(k in name for k in fishing_keywords):
        return 'Fishing'
    if any(k in name for k in agriculture_keywords):
        return 'Agriculture'
    if any(k in name for k in mining_keywords):
        return 'Mining / Resources'
    if any(k in name for k in construction_keywords):
        return 'Construction / Engineering'
    if any(k in name for k in hospitality_keywords):
        return 'Hospitality'
    if any(k in name for k in transport_keywords):
        return 'Transport / Logistics'

    return 'General / Other'

print("\nClassifying sectors...")
df['sector'] = df.apply(classify_sector, axis=1)

# ============================================================
# STEP 6: AGED CARE FLAG
# ============================================================
aged_care_sectors = ['Aged Care']

def is_aged_care(row):
    if row['sector'] == 'Aged Care':
        return True
    name = row['companyname'].lower()
    if any(k in name for k in aged_care_keywords):
        return True
    return False

df['is_aged_care_related'] = df.apply(is_aged_care, axis=1)

print(f"\nAged Care related companies: {df['is_aged_care_related'].sum()}")
print(f"  - Industry - Aged Care LA: {len(df[df['latype'] == 'Industry - Aged Care'])}")
dama_aged = df[(df['is_aged_care_related']) & (df['la_category'] == 'DAMA')]
print(f"  - DAMA companies with aged care names: {len(dama_aged)}")

# ============================================================
# STEP 7: PRINT SUMMARY
# ============================================================
print("\n" + "="*60)
print("SECTOR BREAKDOWN:")
print(df['sector'].value_counts().to_string())

print("\nLA CATEGORY BREAKDOWN:")
print(df['la_category'].value_counts().to_string())

print("\nAGED CARE COMPANIES BY DAMA REGION:")
aged_care_df = df[df['is_aged_care_related']]
print(aged_care_df.groupby('la_category')['companyname'].count().to_string())

print("\nAGED CARE DAMA COMPANIES BY REGION:")
dama_aged_care = df[(df['is_aged_care_related']) & (df['la_category'] == 'DAMA')]
print(dama_aged_care.groupby('latype')['companyname'].count().to_string())

# Save to module-level variable for Phase 4
print("\nData preparation complete. Ready for Excel output.")
df.to_csv(r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\la_classified_temp.csv', index=False)
print("Temp CSV saved.")
