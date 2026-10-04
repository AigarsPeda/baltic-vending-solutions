#!/usr/bin/env python3
"""Exercise deployment guards and transfer ordering without network or WordPress."""
import json
import os
from pathlib import Path
import shlex
import shutil
import socket
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[2]
SCRIPTS = sorted((ROOT / 'scripts').glob('*.sh'))


def run(script, args, env, ok=True):
    result = subprocess.run(['bash', str(script), *args], env=env, text=True, capture_output=True)
    assert (result.returncode == 0) == ok, (script.name, args, result.stdout, result.stderr)
    return result


with tempfile.TemporaryDirectory(prefix='bvs-smoke-') as temp:
    base = Path(temp)
    bin_dir = base / 'bin'
    bin_dir.mkdir()
    log = base / 'commands.jsonl'
    mock = bin_dir / 'mock.py'
    mock.write_text('''#!/usr/bin/env python3
import hashlib, json, os, pathlib, sys
name = pathlib.Path(sys.argv[0]).name
args = sys.argv[1:]
with open(os.environ['MOCK_LOG'], 'a') as f: f.write(json.dumps([name, args]) + '\\n')
joined = ' '.join(args)
if name == 'ssh':
    command = args[-1]
    if 'mktemp -d' in command: print('/var/backups/bvs/fixture')
    elif 'mktemp /tmp/' in command: print('/tmp/bvs-fixture.sql')
    elif 'option get home' in command: print('https://example.invalid')
    elif 'sha256sum' in command: print(hashlib.sha256(pathlib.Path(os.environ['MOCK_THEME'], 'functions.php').read_bytes()).hexdigest())
    elif 'db export - ' in command: print('CREATE TABLE fixture (id int);')
elif name == 'php':
    if 'option get home' in joined: print('http://bvs.local')
    elif 'db export' in joined:
        target = args[args.index('export') + 1]
        pathlib.Path(target).write_text('CREATE TABLE fixture (id int);')
    elif 'search-replace' in args:
        for arg in args:
            if arg.startswith('--export='): pathlib.Path(arg.split('=', 1)[1]).write_text('CREATE TABLE fixture (id int);')
''')
    mock.chmod(0o755)
    for name in ['ssh', 'rsync', 'scp', 'curl', 'php']:
        (bin_dir / name).symlink_to(mock)
    wp = base / 'Local Sites' / 'bvs' / 'app' / 'public'
    for directory in ['themes', 'plugins', 'mu-plugins', 'uploads']:
        (wp / 'wp-content' / directory).mkdir(parents=True)
    (wp / 'wp-config.php').touch()
    theme = base / 'theme'
    theme.mkdir()
    (theme / 'style.css').write_text('/* Theme Name: BVS */\n')
    (theme / 'functions.php').write_text('<?php\n')
    ini = base / 'php.ini'
    ini.touch()
    phar = base / 'wp-cli.phar'
    phar.touch()
    key = base / 'ssh key'
    key.touch()
    for name in ['mysql', 'mysqldump']:
        p = bin_dir / name
        p.write_text('#!/bin/sh\nexit 0\n')
        p.chmod(0o755)
    sock = socket.socket(socket.AF_UNIX)
    # Keep socket path short for macOS.
    socket_path = base / 'mysql.sock'
    sock.bind(str(socket_path))
    values = dict(SSH_KEY=str(key), REMOTE_HOST='root@example.invalid',
                  REMOTE_WP_PATH='/var/www/bvs', REMOTE_BACKUP_DIR='/var/backups/bvs',
                  REMOTE_URL='https://example.invalid', LOCAL_URL='http://bvs.local',
                  LOCAL_WP_PATH=str(wp), LOCAL_PHP_BIN=str(bin_dir / 'php'),
                  LOCAL_PHP_INI=str(ini), LOCAL_WP_CLI=str(phar),
                  LOCAL_MYSQL_BIN_DIR=str(bin_dir), LOCAL_MYSQL_SOCKET=str(socket_path),
                  BACKUP_DIR=str(base / 'backups'), LOCAL_THEME_PATH=str(theme))
    config = base / 'fixture.env'
    config.write_text('\n'.join(k + '=' + shlex.quote(v) for k, v in values.items()) + '\n')
    env = dict(os.environ, PATH=str(bin_dir) + ':' + os.environ['PATH'],
               CONFIG_FILE=str(config), MOCK_LOG=str(log), MOCK_THEME=str(theme))
    empty_env = dict(env, CONFIG_FILE=str(ROOT / 'scripts/baltic-vending-solutions.env.example'))
    for script in SCRIPTS:
        subprocess.run(['bash', '-n', str(script)], check=True)
        run(script, ['--help'], empty_env)
        failure = run(script, ['--dry-run'], empty_env, ok=False)
        assert 'is blank' in failure.stderr, failure.stderr
        run(script, ['--unknown'], empty_env, ok=False)
    assert not log.exists(), 'Blank configuration must not call SSH/WP-CLI/rsync.'
    for script in SCRIPTS:
        log.write_text('')
        run(script, ['--dry-run'], env)
        events = [json.loads(line) for line in log.read_text().splitlines()]
        for name, args in events:
            joined = ' '.join(args)
            if name == 'rsync': assert '--dry-run' in args
            assert not any(token in joined for token in ['db import', 'db export', 'mktemp ', 'mkdir ', 'chown ', 'tar -czf', 'option update']), (script.name, events)
        assert not (wp / 'wp-content/themes/baltic-vending-solutions').exists()
        assert not (base / 'backups').exists()
    # Exercise each script's default configuration filename from a copied fixture.
    fixture_scripts = base / 'project' / 'scripts'
    shutil.copytree(ROOT / 'scripts', fixture_scripts)
    shutil.copyfile(config, fixture_scripts / 'baltic-vending-solutions.env')
    default_env = dict(env)
    default_env.pop('CONFIG_FILE')
    for script in SCRIPTS:
        run(fixture_scripts / script.name, ['--dry-run'], default_env)
    # Validate the rejection of unsafe remote paths before invoking network tools.
    for bad in ['/var/www/bvs/../other', '/var/www/bvs;echo', '/var/www/bvs/']:
        config.write_text('\n'.join(k + '=' + shlex.quote(bad if k == 'REMOTE_WP_PATH' else v) for k, v in values.items()))
        log.write_text('')
        run(ROOT / 'scripts/sync-code-to-droplet.sh', ['--dry-run'], env, ok=False)
        assert log.read_text() == ''
    config.write_text('\n'.join(k + '=' + shlex.quote(v) for k, v in values.items()))
    # Apply paths use mocked tools. Check backups precede replacement.
    for filename in ['sync-code-to-droplet.sh', 'sync-plugins-to-droplet.sh', 'push-db-to-droplet.sh', 'pull-db-from-droplet.sh']:
        log.write_text('')
        args = ['--yes'] if '-db-' in filename else []
        run(ROOT / 'scripts' / filename, args, env)
        events = [json.loads(line) for line in log.read_text().splitlines()]
        commands = [(name, ' '.join(args)) for name, args in events]
        if filename == 'push-db-to-droplet.sh':
            backup = next(i for i, (name, cmd) in enumerate(commands) if name == 'ssh' and 'db export' in cmd)
            mutation = next(i for i, (name, cmd) in enumerate(commands) if name == 'ssh' and 'db import' in cmd)
            assert backup < mutation
            local = [cmd for name, cmd in commands if name == 'php']
            assert not any('db import' in cmd for cmd in local)
            assert all('--export=' in cmd for cmd in local if 'search-replace' in cmd)
        elif filename == 'pull-db-from-droplet.sh':
            backup = next(i for i, (name, cmd) in enumerate(commands) if name == 'php' and 'db export' in cmd)
            mutation = next(i for i, (name, cmd) in enumerate(commands) if name == 'php' and 'db import' in cmd)
            assert backup < mutation
            assert list((base / 'backups').glob('*.sql.gz'))
        else:
            backup = next(i for i, (name, cmd) in enumerate(commands) if name == 'ssh' and 'tar -czf' in cmd)
            mutation = next(i for i, (name, cmd) in enumerate(commands) if name == 'rsync')
            assert backup < mutation
            if filename == 'sync-plugins-to-droplet.sh':
                assert all('--delete' not in cmd for name, cmd in commands if name == 'rsync')
    # Exercise actual installed rsync for Local copy/deletion and SSH key quoting.
    real_rsync = shutil.which('rsync', path=os.environ['PATH'])
    log.write_text('')
    subprocess.run([real_rsync, '--dry-run', '-e', f'ssh -i "{key}" -o BatchMode=yes',
                    str(theme) + '/', 'root@example.invalid:/var/www/bvs/'],
                   env=env, text=True, capture_output=True)
    ssh_event = next(json.loads(line) for line in log.read_text().splitlines()
                     if json.loads(line)[0] == 'ssh')
    assert ssh_event[1][ssh_event[1].index('-i') + 1] == str(key)
    real_env = dict(env, PATH=os.environ['PATH'])
    local_script = ROOT / 'scripts/sync-code-to-local.sh'
    destination = wp / 'wp-content/themes/baltic-vending-solutions'
    run(local_script, ['--dry-run'], real_env)
    assert not destination.exists()
    destination.mkdir()
    (destination / 'obsolete.php').write_text('obsolete')
    other_theme = wp / 'wp-content/themes/other-theme'
    other_theme.mkdir()
    (other_theme / 'keep.txt').write_text('keep')
    run(local_script, [], real_env)
    assert (destination / 'functions.php').read_bytes() == (theme / 'functions.php').read_bytes()
    assert not (destination / 'obsolete.php').exists()
    assert (other_theme / 'keep.txt').read_text() == 'keep'
    sock.close()
print('Passed: shell syntax, help, blank-config guards, dry-run, path guards and backup ordering for all six scripts; real Local rsync and SSH key quoting.')
