import pytest
from playwright.sync_api import Page, expect

def test_admin_settings_change(page: Page, base_url: str):
    # Go to login page
    page.goto(f"{base_url}/wp-login.php")
    
    # Check if we need to log in
    if page.locator("#user_login").is_visible():
        page.fill("#user_login", "admin")
        page.fill("#user_pass", "password")
        page.click("#wp-submit")
    
    # Wait for admin dashboard
    page.wait_for_url("**/wp-admin/**")
    
    # Navigate to Run Log options page
    page.goto(f"{base_url}/wp-admin/edit.php?post_type=oi_run_log_post&page=oirl-options-menu")
    
    # Verify title
    expect(page.locator("h3")).to_contain_text("Run Log Options")
    
    # Locate radio buttons
    km_radio = page.locator("#oirl-distance-unit-km")
    mi_radio = page.locator("#oirl-distance-unit-mi")
    
    # Toggle to miles (mi) and save
    mi_radio.check()
    page.click('input[name="Submit"]')
    
    # Verify success notice is displayed
    expect(page.locator(".updated")).to_contain_text("Options saved")
    expect(mi_radio).to_be_checked()
    expect(km_radio).not_to_be_checked()
    
    # Toggle back to kilometers (km) and save
    km_radio.check()
    page.click('input[name="Submit"]')
    
    # Verify success notice again
    expect(page.locator(".updated")).to_contain_text("Options saved")
    expect(km_radio).to_be_checked()
    expect(mi_radio).not_to_be_checked()
