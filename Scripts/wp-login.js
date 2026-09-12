const { chromium } = require('playwright');

(async () => {
  const browser = await chromium.launch({ headless: false });
  const page = await browser.newPage();

  await page.goto('http://localhost:8888/wp-login.php', { waitUntil: 'networkidle' });

  await page.locator('#user_login').click();
  await page.locator('#user_login').fill('admin');

  await page.locator('#user_pass').click();
  await page.locator('#user_pass').fill('Password123!');

  await page.locator('#wp-submit').click();

  await page.waitForLoadState('networkidle');
  console.log('Login attempt complete. Current URL:', page.url());

  await browser.close();
})();
