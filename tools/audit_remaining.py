#!/usr/bin/env python3
import json
import pathlib
import re

ROOT = pathlib.Path(__file__).resolve().parents[1]
TEXT_SUFFIXES = {'.php', '.blade.php', '.js', '.ts', '.vue', '.json', '.yml', '.yaml', '.md'}


def is_text(path: pathlib.Path) -> bool:
    name = path.name.lower()
    return any(name.endswith(suffix) for suffix in TEXT_SUFFIXES) or name in {'composer.json', 'composer.lock', 'w'}


def iter_text_files():
    ignored = {'.git', 'vendor', 'node_modules', 'storage', 'bootstrap/cache'}
    for path in ROOT.rglob('*'):
        if not path.is_file():
            continue
        rel = path.relative_to(ROOT)
        if any(str(rel).startswith(prefix + '/') for prefix in ignored):
            continue
        if is_text(path):
            yield path


def grep(pattern: str, roots=None, flags=0):
    rx = re.compile(pattern, flags)
    results = []
    files = list(iter_text_files()) if roots is None else []
    if roots is not None:
        for root in roots:
            p = ROOT / root
            if p.is_file():
                files.append(p)
            elif p.exists():
                files.extend(x for x in p.rglob('*') if x.is_file() and is_text(x))
    for path in files:
        try:
            text = path.read_text(encoding='utf-8', errors='ignore')
        except Exception:
            continue
        for n, line in enumerate(text.splitlines(), 1):
            if rx.search(line):
                results.append({'file': str(path.relative_to(ROOT)), 'line': n, 'text': line.strip()})
    return results


route_file = ROOT / 'routes/web.php'
route_text = route_file.read_text(encoding='utf-8')
route_rx = re.compile(r"Route::get\(\s*['\"]([^'\"]+)['\"]\s*,\s*\[([^\]]+)\]\)\s*->name\(\s*['\"]([^'\"]+)['\"]\s*\)")
mutator_words = re.compile(r'(store|save|update|delete|destroy|cancel|toggle|change|convert|duplicate|remind|sign|approve|reject|hide|empty|clock|markread|reset|restore|archive)', re.I)
mutating_gets = []
for n, line in enumerate(route_text.splitlines(), 1):
    m = route_rx.search(line)
    if not m:
        continue
    uri, action, name = m.groups()
    method = action.split(',')[-1].strip().strip("'\" ")
    if mutator_words.search(method) or mutator_words.search(uri):
        mutating_gets.append({'line': n, 'uri': uri, 'action': action.strip(), 'method': method, 'name': name})

references = {}
for route in mutating_gets:
    needle = route['name']
    hits = grep(re.escape(needle), roots=['resources', 'app', 'routes'])
    references[needle] = [h for h in hits if h['file'] != 'routes/web.php']

composer = json.loads((ROOT / 'composer.json').read_text(encoding='utf-8'))
lock = json.loads((ROOT / 'composer.lock').read_text(encoding='utf-8'))
locked = {p['name']: {'version': p.get('version'), 'reference': (p.get('source') or {}).get('reference')} for p in lock.get('packages', []) + lock.get('packages-dev', [])}
unstable = []
for section in ('require', 'require-dev'):
    for package, constraint in composer.get(section, {}).items():
        if constraint == '*' or constraint.startswith('dev-') or '*' in constraint:
            unstable.append({'section': section, 'package': package, 'constraint': constraint, 'locked': locked.get(package)})

webhooks = grep(r'webhook', roots=['routes', 'app/Http/Controllers', 'app/Http/Middleware'], flags=re.I)
date_patterns = grep(r'(->format\(|translatedFormat\(|Carbon::parse\(|format_date\(|date_format\()', roots=['resources/views'], flags=re.I)
device_migrations = [str(p.relative_to(ROOT)) for p in (ROOT / 'database/migrations').glob('*.php') if 'device' in p.name.lower()]
tests = [str(p.relative_to(ROOT)) for p in (ROOT / 'tests').rglob('*.php')]

report = {
    'mutating_get_routes': mutating_gets,
    'route_references': references,
    'unstable_direct_dependencies': unstable,
    'webhook_mentions': webhooks,
    'date_rendering_mentions_count': len(date_patterns),
    'date_rendering_mentions_sample': date_patterns[:200],
    'device_migrations': device_migrations,
    'root_w_exists': (ROOT / 'w').exists(),
    'tests': tests,
}
print(json.dumps(report, ensure_ascii=False, indent=2))
