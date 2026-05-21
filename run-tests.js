const { spawnSync } = require('child_process');

function runCommand(command, args, options = {}) {
  console.log(`\n> Running: ${command} ${args.join(' ')}`);
  const result = spawnSync(command, args, { stdio: 'inherit', shell: true, ...options });
  if (result.status !== 0) {
    console.error(`Command failed with status ${result.status}: ${command} ${args.join(' ')}`);
    return false;
  }
  return true;
}

console.log('Starting WordPress environment...');
if (!runCommand('npx', ['wp-env', 'start'])) {
  console.error('Failed to start wp-env.');
  process.exit(1);
}

let testsPassed = true;

try {
  // 1. Run PHPUnit tests
  console.log('\n--- Running PHPUnit Tests ---');
  const phpunitSuccess = runCommand('npx', [
    'wp-env', 'run', 'tests-cli',
    '--env-cwd=wp-content/plugins/run-log',
    './vendor/bin/phpunit'
  ]);
  if (!phpunitSuccess) {
    testsPassed = false;
  }

  // 2. Prepare test database for E2E tests (activate theme & plugin)
  console.log('\n--- Preparing E2E Test Database Environment ---');
  const prepTheme = runCommand('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'theme', 'activate', 'twentytwentyfive']);
  const prepPlugin = runCommand('npx', ['wp-env', 'run', 'tests-cli', 'wp', 'plugin', 'activate', 'run-log']);
  if (!prepTheme || !prepPlugin) {
    console.warn('Warning: Failed to prepare E2E test database environment.');
  }

  // 3. Run E2E Playwright tests
  console.log('\n--- Running E2E Playwright Tests ---');
  // Use .venv\Scripts\pytest on Windows, .venv/bin/pytest on others
  const pytestCmd = process.platform === 'win32' ? '.venv\\Scripts\\pytest' : '.venv/bin/pytest';
  const e2eSuccess = runCommand(pytestCmd, ['tests/e2e', '--base-url', 'http://localhost:8889']);
  if (!e2eSuccess) {
    testsPassed = false;
  }
} catch (err) {
  console.error('An error occurred during test execution:', err);
  testsPassed = false;
} finally {
  // 4. Shutdown environment at the end
  console.log('\n--- Shutting Down WordPress Environment ---');
  runCommand('npx', ['wp-env', 'stop']);
}

if (!testsPassed) {
  console.error('\nTest suite execution failed.');
  process.exit(1);
} else {
  console.log('\nAll tests completed successfully!');
  process.exit(0);
}
