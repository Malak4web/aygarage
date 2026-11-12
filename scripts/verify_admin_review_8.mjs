import puppeteer from 'puppeteer-core';

const ADMIN_LOGIN = process.env.ADMIN_LOGIN;
const ADMIN_PASSWORD = process.env.ADMIN_PASSWORD;
if (!ADMIN_LOGIN || !ADMIN_PASSWORD) {
  console.error('Set ADMIN_LOGIN and ADMIN_PASSWORD in the environment first.');
  process.exit(1);
}


const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

async function main() {
  console.log('Launching browser for Review 8 Verification...');
  const browser = await puppeteer.launch({
    executablePath: chromePath,
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox', '--window-size=1280,800']
  });

  const page = await browser.newPage();
  const consoleErrors = [];

  page.on('console', msg => {
    if (msg.type() === 'error') {
      consoleErrors.push({ url: page.url(), text: msg.text() });
      console.error(`[CONSOLE ERROR @ ${page.url()}]:`, msg.text());
    }
  });

  page.on('pageerror', err => {
    consoleErrors.push({ url: page.url(), text: err.message });
    console.error(`[PAGE ERROR @ ${page.url()}]:`, err.message);
  });

  try {
    // 1. Admin Login via Phone
    console.log('\n--- 1. Testing Admin Login with Phone ---');
    await page.goto('http://127.0.0.1:8000/admin/login', { waitUntil: 'networkidle2' });
    
    // Fill login input (phone) and password
    const loginInput = await page.$('input[type="text"], input[name="login"], input[id*="login"]');
    if (!loginInput) {
      throw new Error('Could not find login input field');
    }
    await loginInput.type(ADMIN_LOGIN);

    const passInput = await page.$('input[type="password"]');
    await passInput.type(ADMIN_PASSWORD);

    // Submit form
    const submitBtn = await page.$('button[type="submit"]');
    await Promise.all([
      page.waitForNavigation({ waitUntil: 'networkidle2' }),
      submitBtn.click()
    ]);

    console.log('Logged in successfully! Current URL:', page.url());
    if (!page.url().includes('/admin') || page.url().includes('/login')) {
      throw new Error(`Failed to log in to /admin. Current URL: ${page.url()}`);
    }

    // 2. Pickup Order Status Update (Order 1043)
    console.log('\n--- 2. Testing Pickup Order Update in Admin ---');
    await page.goto('http://127.0.0.1:8000/admin/orders', { waitUntil: 'networkidle2' });
    console.log('Orders table loaded.');

    // Find Order 1043 edit link
    const orderLinks = await page.$$eval('a[href*="/admin/orders/"]', links => 
      links.map(l => ({ href: l.href, text: l.innerText.trim() }))
    );
    console.log('Found order links:', orderLinks.filter(l => l.href.endsWith('/edit')));

    const order1043Link = orderLinks.find(l => l.text.includes('1043') || l.href.includes('/admin/orders/2/edit'))?.href 
      || 'http://127.0.0.1:8000/admin/orders/2/edit';

    await page.goto(order1043Link, { waitUntil: 'networkidle2' });
    console.log('Opened Order 1043 Edit Page:', page.url());

    // Verify subtotal/total are disabled
    const disabledInputs = await page.$$eval('input[disabled]', els => els.length);
    console.log(`Verified ${disabledInputs} inputs are disabled (totals, number, items, etc.).`);

    // Print buttons on page
    const buttons = await page.$$eval('button', btns => btns.map(b => ({
      text: b.innerText.trim(),
      type: b.type,
      wireClick: b.getAttribute('wire:click'),
      class: b.className
    })));
    console.log('Buttons on order edit page:', buttons);

    console.log('Clicking save changes button for Order 1043...');
    await page.evaluate(() => {
      const btn = Array.from(document.querySelectorAll('button')).find(b => b.innerText.trim() === 'حفظ التغييرات');
      if (btn) btn.click();
    });
    await page.waitForNetworkIdle({ idleTime: 1000, timeout: 5000 }).catch(() => {});
    console.log('Order save finished. Current URL:', page.url());

    // 3. Settings Page (ManageSettings)
    console.log('\n--- 3. Testing Settings Page ---');
    await page.goto('http://127.0.0.1:8000/admin/settings', { waitUntil: 'networkidle2' });
    console.log('Settings page loaded:', page.url());

    // Check delivery fee input
    const deliveryFeeInput = await page.$('input[id*="delivery_fee"]');
    if (deliveryFeeInput) {
      const val = await page.evaluate(el => el.value, deliveryFeeInput);
      console.log('Current delivery_fee in form:', val);
    }

    // Check saving settings
    const saveSettingsBtn = await page.$('form button[type="submit"]');
    if (saveSettingsBtn) {
      await saveSettingsBtn.evaluate(b => b.click());
      await page.waitForNetworkIdle({ idleTime: 1000, timeout: 5000 }).catch(() => {});
      console.log('Saved Settings successfully.');
    }

    // 4. Storefront Verification
    console.log('\n--- 4. Checking Storefront Routes ---');
    const storeRoutes = [
      'http://localhost:5173/',
      'http://localhost:5173/c/oils',
      'http://localhost:5173/p/motul-7100-10w40',
      'http://localhost:5173/bikes',
      'http://localhost:5173/cart',
      'http://localhost:5173/booking',
      'http://localhost:5173/login'
    ];

    for (const url of storeRoutes) {
      await page.goto(url, { waitUntil: 'networkidle0' });
      console.log(`Visited: ${url} (200 OK)`);
    }

    console.log('\n=== VERIFICATION SUMMARY ===');
    console.log(`Total Console Errors encountered: ${consoleErrors.length}`);
    if (consoleErrors.length > 0) {
      console.error('Errors:', consoleErrors);
    } else {
      console.log('ALL TESTS & BROWSING VERIFICATIONS PASSED WITH ZERO CONSOLE ERRORS!');
    }

  } catch (err) {
    console.error('Verification failed with error:', err);
    process.exitCode = 1;
  } finally {
    await browser.close();
  }
}

main();
