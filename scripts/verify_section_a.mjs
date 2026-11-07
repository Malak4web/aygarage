import puppeteer from 'puppeteer-core';

const chromePath = 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe';

async function main() {
  console.log('Launching Chrome via puppeteer-core...');
  const browser = await puppeteer.launch({
    executablePath: chromePath,
    headless: 'new',
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  const consoleErrors = [];

  page.on('console', msg => {
    if (msg.type() === 'error') {
      consoleErrors.push({ url: page.url(), text: msg.text() });
      console.error(`[BROWSER ERROR @ ${page.url()}]:`, msg.text());
    }
  });

  page.on('pageerror', err => {
    consoleErrors.push({ url: page.url(), text: err.message });
    console.error(`[PAGE ERROR @ ${page.url()}]:`, err.message);
  });

  page.on('requestfailed', request => {
    console.log(`[REQUEST FAILED]: ${request.url()} - ${request.failure()?.errorText}`);
  });

  console.log('--- Checking all main routes for console errors ---');
  const routes = [
    '/',
    '/c/oils',
    '/p/motul-7100-10w40-4t',
    '/p/yuasa-ytz10s-battery',
    '/bikes',
    '/b/zontes-350t-2023-adv',
    '/cart',
    '/login',
    '/booking',
    '/search?q=motul'
  ];

  for (const r of routes) {
    await page.goto(`http://localhost:5173${r}`, { waitUntil: 'networkidle0' });
    console.log(`Visited: ${r} - Title: ${await page.title()}`);
  }

  console.log('--- Testing Full Guest Flow (Item 3) ---');
  // 1. Clear storage
  await page.evaluate(() => localStorage.clear());

  // 2. Browse category
  await page.goto('http://localhost:5173/c/oils', { waitUntil: 'networkidle0' });
  await page.waitForSelector('.pc button.btn.sm');

  // 3. Add to cart
  const addBtn = await page.$('.pc button.btn.sm');
  if (!addBtn) throw new Error('No add button found on category page');
  await addBtn.click();
  await new Promise(r => setTimeout(r, 600));

  // Check cart badge in header
  const cartBadge = await page.$eval('.cart-badge', el => el.textContent.trim());
  console.log('Cart count badge:', cartBadge);

  // 4. Navigate to cart
  await page.goto('http://localhost:5173/cart', { waitUntil: 'networkidle0' });
  await page.waitForSelector('.line');
  const linesCount = await page.$$eval('.line', els => els.length);
  console.log('Cart lines count:', linesCount);
  if (linesCount === 0) throw new Error('Cart is empty after adding item');

  // Check OrderSummary exists and button exists
  const checkoutBtn = await page.$('a.checkout-btn, a[href="/checkout"]');
  if (!checkoutBtn) throw new Error('OrderSummary checkout button not found!');
  console.log('OrderSummary & Checkout button rendered properly!');

  // 5. Click checkout -> expect redirect to login?redirect=/checkout
  await checkoutBtn.click();
  await page.waitForFunction(() => window.location.pathname === '/login', { timeout: 5000 });
  console.log('Current URL after checkout click:', page.url());
  if (!page.url().includes('/login')) throw new Error('Did not redirect to login');

  // 6. Login
  await page.waitForSelector('.auth-card input');
  const inputs = await page.$$('.auth-card input');
  await inputs[0].type('01012345678');
  await inputs[1].type('password123');
  const submitBtn = await page.$('.auth-card button[type="submit"]');
  await submitBtn.click();
  await page.waitForFunction(() => window.location.pathname === '/checkout', { timeout: 5000 });
  console.log('Current URL after login:', page.url());
  if (!page.url().includes('/checkout')) throw new Error('Did not return to /checkout after login');

  // 7. Fill checkout form & submit
  await page.waitForSelector('.fl .form-input');
  const formInputs = await page.$$('.fl .form-input');
  await formInputs[0].click({ clickCount: 3 });
  await formInputs[0].type('أحمد السيد');
  await formInputs[1].click({ clickCount: 3 });
  await formInputs[1].type('01012345678');
  await formInputs[2].click({ clickCount: 3 });
  await formInputs[2].type('15 شارع التحرير الدقي');

  const confirmBtn = await page.$('button.confirm-btn');
  if (!confirmBtn) throw new Error('Confirm order button not found on checkout');
  await confirmBtn.click();
  await page.waitForFunction(() => window.location.pathname.startsWith('/order/'), { timeout: 8000 });
  console.log('Current URL after order confirmation:', page.url());
  if (!page.url().includes('/order/')) throw new Error('Did not redirect to /order/:id');

  // 8. Order success page shows items and totals
  await page.waitForSelector('.order-num');
  const orderNumber = await page.$eval('.order-num', el => el.textContent.trim());
  console.log('Order created successfully! Number:', orderNumber);

  // 9. Check order appears in Account > طلباتي
  await page.goto('http://localhost:5173/account?tab=orders', { waitUntil: 'networkidle0' });
  await page.waitForSelector('tbody tr');
  const orderRows = await page.$$eval('tbody tr', els => els.length);
  console.log('Orders appearing in Account:', orderRows);
  if (orderRows === 0) throw new Error('Order does not appear in Account!');

  // 10. Check booking flow
  console.log('--- Testing Booking Flow (Item 9 & 22 & Round 3 Item 1) ---');
  await page.goto('http://localhost:5173/booking', { waitUntil: 'networkidle0' });
  const summaryCost = await page.$eval('.sum .dl:nth-of-type(5) b', el => el.textContent.trim());
  console.log('Booking summary cost on page:', summaryCost);
  if (summaryCost === '—' || !summaryCost) throw new Error('Booking summary cost is empty/dash!');

  const confirmBookingBtn = await page.$('button.confirm-btn');
  if (!confirmBookingBtn) throw new Error('Confirm booking button not found');
  await confirmBookingBtn.click();
  await page.waitForSelector('.confirmed-card', { timeout: 8000 });
  const refText = await page.$eval('.ref-text', el => el.textContent.trim());
  console.log('Booking confirmed successfully:', refText);

  // 11. Check booking appears in Account > حجوزاتي with cost
  await page.goto('http://localhost:5173/account?tab=bookings', { waitUntil: 'networkidle0' });
  await page.waitForSelector('tbody tr');
  const bookingRows = await page.$$eval('tbody tr', els => els.length);
  console.log('Bookings appearing in Account:', bookingRows);
  if (bookingRows === 0) throw new Error('Booking does not appear in Account!');
  const firstBookingCost = await page.$eval('tbody tr td:last-child', el => el.textContent.trim());
  console.log('Account booking cost column:', firstBookingCost);
  if (firstBookingCost === '—' || !firstBookingCost) throw new Error('Account booking cost is empty/dash!');

  console.log('\n========================================');
  console.log(`TOTAL BROWSER CONSOLE ERRORS: ${consoleErrors.length}`);
  if (consoleErrors.length > 0) {
    console.error('Console errors encountered:', consoleErrors);
  } else {
    console.log('✓ CONSOLE IS COMPLETELY CLEAN ON EVERY PAGE!');
  }
  console.log('========================================\n');

  await browser.close();
  process.exit(consoleErrors.length > 0 ? 1 : 0);
}

main().catch(err => {
  console.error('Script failed:', err);
  process.exit(1);
});
