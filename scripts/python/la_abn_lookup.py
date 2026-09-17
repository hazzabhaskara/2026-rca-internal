"""
ABN Lookup Module — Scrape Australian Business Register
Mendapatkan State/Region + Semua lokasi per perusahaan
Cache-enabled untuk menghindari re-scrape
"""

import requests
import json
import time
import re
import os
from bs4 import BeautifulSoup

CACHE_FILE = r'c:\laragon\www\Ujicoba_RembulanCitraAbadi\DAMA\abn_cache.json'
BASE_URL = 'https://abr.business.gov.au/Search/ResultsActive'
HEADERS = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 Chrome/120.0.0.0 Safari/537.36',
    'Accept': 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
    'Accept-Language': 'en-AU,en;q=0.9',
}

VALID_STATES = {'NSW', 'VIC', 'QLD', 'SA', 'WA', 'TAS', 'NT', 'ACT'}


def load_cache():
    if os.path.exists(CACHE_FILE):
        with open(CACHE_FILE, 'r', encoding='utf-8') as f:
            return json.load(f)
    return {}


def save_cache(cache):
    os.makedirs(os.path.dirname(CACHE_FILE), exist_ok=True)
    with open(CACHE_FILE, 'w', encoding='utf-8') as f:
        json.dump(cache, f, indent=2, ensure_ascii=False)


def clean_company_name(name):
    """Strip common suffixes for better matching."""
    name = name.strip().upper()
    for suffix in [' PTY LTD', ' PTY. LTD', ' PTY LIMITED', ' LIMITED', ' LTD',
                   ' INC', ' INC.', ' INCORPORATED', ' CO.', ' COMPANY',
                   ' (VIC)', ' (NSW)', ' (QLD)', ' (SA)', ' (WA)', ' (NT)',
                   ' (TAS)', ' (ACT)', ' (AUSTRALIA)', ' (AUST)', ' (SA) PTY LTD']:
        name = name.replace(suffix, '')
    return name.strip()


def parse_abn_results(html, company_name):
    """
    Parse ABN Lookup HTML table.
    Returns list of all matching locations:
    [{'abn': ..., 'name': ..., 'state': ..., 'postcode': ...}, ...]
    """
    soup = BeautifulSoup(html, 'html.parser')
    rows = soup.find_all('tr')

    results = []
    clean_input = clean_company_name(company_name)

    for row in rows:
        cells = row.find_all('td')
        if len(cells) < 4:
            continue

        text = row.get_text(separator='|', strip=True)
        parts = [p.strip() for p in text.split('|') if p.strip()]

        # Expected format: ABN | Status | Name | Type | Postcode State
        if len(parts) < 4:
            continue

        # Find state in last cell
        last = parts[-1]
        state_match = re.search(r'\b(NSW|VIC|QLD|SA|WA|TAS|NT|ACT)\b', last)
        postcode_match = re.search(r'\b(\d{4})\b', last)

        if not state_match:
            continue

        state = state_match.group(1)
        postcode = postcode_match.group(1) if postcode_match else ''

        # Get name (usually 3rd part, after ABN and Status)
        name_parts = [p for p in parts if not re.match(r'^\d[\d\s]+$', p)
                      and p not in ('Active', 'Cancelled', 'Entity Name', 'Business Name', last)]
        found_name = name_parts[0] if name_parts else ''

        # Check similarity with input
        clean_found = clean_company_name(found_name)
        # Include if name is a substring match or exact
        is_match = (
            clean_input in clean_found or
            clean_found in clean_input or
            clean_input[:min(10, len(clean_input))] in clean_found
        )

        if is_match or len(results) == 0:  # always include first result as fallback
            results.append({
                'name': found_name,
                'state': state,
                'postcode': postcode,
            })

    return results


def lookup_company(company_name, cache, delay=1.0):
    """
    Lookup a company in ABN register.
    Returns dict with 'states' (list of all states found), 'postcodes'
    Uses cache to avoid repeated requests.
    """
    cache_key = company_name.upper().strip()

    if cache_key in cache:
        return cache[cache_key]

    try:
        r = requests.get(BASE_URL,
                         params={'SearchText': company_name},
                         headers=HEADERS,
                         timeout=12)

        if r.status_code != 200:
            result = {'states': [], 'postcodes': [], 'error': f'HTTP {r.status_code}'}
        else:
            matches = parse_abn_results(r.text, company_name)
            # Collect unique states
            states = list(dict.fromkeys([m['state'] for m in matches if m['state'] in VALID_STATES]))
            postcodes = list(dict.fromkeys([m['postcode'] for m in matches if m['postcode']]))
            result = {
                'states': states,
                'postcodes': postcodes[:3],  # max 3 postcodes
                'raw_count': len(matches),
            }

    except Exception as e:
        result = {'states': [], 'postcodes': [], 'error': str(e)}

    cache[cache_key] = result
    save_cache(cache)
    time.sleep(delay)
    return result


def batch_lookup(company_names, delay=0.8, verbose=True):
    """
    Batch lookup for a list of company names.
    Returns dict: {company_name: result}
    """
    cache = load_cache()
    results = {}
    total = len(company_names)

    cached_count = sum(1 for n in company_names if n.upper().strip() in cache)
    if verbose:
        print(f"  Found {cached_count}/{total} already cached. Need to fetch {total - cached_count}.")

    for i, name in enumerate(company_names, 1):
        result = lookup_company(name, cache, delay=delay)
        results[name] = result

        if verbose and i % 10 == 0:
            states_found = sum(1 for r in results.values() if r.get('states'))
            print(f"  Progress: {i}/{total} | Found states for {states_found} companies...")

    return results


if __name__ == '__main__':
    # Quick test
    test_companies = ['HAMMONDCARE', 'Queensland Police Service', 'Care Services Australia Limited', 'Warrigal Care']
    print("Testing ABN Lookup module...")
    results = batch_lookup(test_companies, delay=1.0)
    for name, res in results.items():
        print(f"  {name}: states={res.get('states', [])} postcodes={res.get('postcodes', [])}")
