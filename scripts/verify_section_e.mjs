import puppeteer from 'puppeteer-core';

async function main() {
  const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: true,
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  await page.setViewport({ width: 1280, height: 800 });

  console.log('--- Testing Section E Accessibility ---');

  // 1. Test Product Page bike picker modal + Esc key + scroll lock
  await page.goto('http://localhost:5173/p/motul-7100-10w40', { waitUntil: 'networkidle0' });
  const changeBikeBtn = await page.$('.change-bike-btn');
  if (!changeBikeBtn) throw new Error('Change bike button not found');
  await changeBikeBtn.click();
  await page.waitForSelector('.modal-card');

  // Check body overflow is locked
  let bodyOverflow = await page.evaluate(() => document.body.style.overflow);
  console.log('Body overflow with modal open:', bodyOverflow);
  if (bodyOverflow !== 'hidden') throw new Error('Body scroll was not locked when modal opened!');

  // Press Escape
  await page.keyboard.press('Escape');
  await page.waitForFunction(() => !document.querySelector('.modal-card'), { timeout: 3000 });
  bodyOverflow = await page.evaluate(() => document.body.style.overflow);
  console.log('Body overflow after modal closed via Escape:', bodyOverflow);
  if (bodyOverflow === 'hidden') throw new Error('Body scroll was not unlocked after modal closed!');

  // 2. Test Mobile Drawer + Esc key + scroll lock on mobile
  await page.setViewport({ width: 375, height: 667 });
  await page.goto('http://localhost:5173/', { waitUntil: 'networkidle0' });
  const menuBtn = await page.$('.mobile-menu-btn');
  if (!menuBtn) throw new Error('Mobile menu button not found');
  await menuBtn.click();
  await page.waitForSelector('.mobile-drawer');

  bodyOverflow = await page.evaluate(() => document.body.style.overflow);
  console.log('Body overflow with drawer open:', bodyOverflow);
  if (bodyOverflow !== 'hidden') throw new Error('Body scroll not locked for drawer!');

  await page.keyboard.press('Escape');
  await page.waitForFunction(() => !document.querySelector('.mobile-drawer'), { timeout: 3000 });
  bodyOverflow = await page.evaluate(() => document.body.style.overflow);
  console.log('Body overflow after drawer closed via Escape:', bodyOverflow);
  if (bodyOverflow === 'hidden') throw new Error('Body scroll not unlocked after drawer closed!');

  console.log('\n✓ ALL SECTION E ACCESSIBILITY TESTS PASSED!\n');
  await browser.close();
}

main().catch(err => {
  console.error('Section E test failed:', err);
  process.exit(1);
});
