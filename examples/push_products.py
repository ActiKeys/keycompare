#!/usr/bin/env python3
"""
Example Python script that sends products to KeyCompare Laravel API.

Usage:
    python push_products.py input.json
    python push_products.py input.json --url http://keycompare.local/api/import --token SECRET
"""
import argparse
import json
import sys
import urllib.request
import urllib.error


def push(data: dict, url: str, token: str | None = None) -> dict:
    """POST the data to the KeyCompare API."""
    payload = json.dumps(data).encode("utf-8")
    headers = {"Content-Type": "application/json"}
    if token:
        headers["Authorization"] = f"Bearer {token}"

    req = urllib.request.Request(url, data=payload, headers=headers, method="POST")
    try:
        with urllib.request.urlopen(req, timeout=120) as resp:
            return json.loads(resp.read().decode("utf-8"))
    except urllib.error.HTTPError as e:
        return json.loads(e.read().decode("utf-8") or '{"ok": false, "error": "http error"}')


def main():
    p = argparse.ArgumentParser()
    p.add_argument("file", help="Path to JSON file with products array")
    p.add_argument("--url", default="http://127.0.0.1:8001/api/import", help="Import endpoint URL")
    p.add_argument("--token", help="Bearer token (if IMPORT_API_TOKEN is set on server)")
    args = p.parse_args()

    with open(args.file) as f:
        data = json.load(f)

    products = data.get("products", [])
    if not products:
        print("No products found in input file", file=sys.stderr)
        sys.exit(1)

    print(f"Pushing {len(products)} products to {args.url}...")
    result = push(data, args.url, args.token)

    if result.get("ok"):
        p = result.get("products", {})
        print(f"✓ OK in {result.get('duration_ms')}ms")
        print(f"  Products: {p.get('total')} (created: {p.get('created')}, updated: {p.get('updated')})")
    else:
        print(f"✗ FAILED: {result}", file=sys.stderr)
        sys.exit(2)


if __name__ == "__main__":
    main()
