import re
import pytest
from datetime import datetime
from playwright.sync_api import Page, expect

def test_strava_garmin_embeds(page: Page, base_url: str):
    # Go to login page
    page.goto(f"{base_url}/wp-login.php")
    
    # Check if we need to log in
    if page.locator("#user_login").is_visible():
        page.fill("#user_login", "admin")
        page.fill("#user_pass", "password")
        page.click("#wp-submit")
    
    # Wait for admin dashboard
    page.wait_for_url("**/wp-admin/**")
    
    # Generate unique titles with timestamps
    now = datetime.now()
    timestamp = now.strftime("%y%m%d%H%M%S")
    strava_post_title = f"Strava Run {timestamp}"
    garmin_post_title = f"Garmin Run {timestamp}"
    
    strava_activity_id = "903315432"
    garmin_activity_id = "1007450461"
    
    # --- 1. Create Strava Embed Post ---
    page.goto(f"{base_url}/wp-admin/post-new.php?post_type=oi_run_log_post")
    page.fill("#title", strava_post_title)
    
    # Switch to Text/HTML view for post editor and fill content
    if page.locator("#content-html").is_visible():
        page.click("#content-html")
    page.fill("#content", f"This is an E2E test verifying Strava embed.")
    
    # TODO: In the future, enforce setting Distance and Duration values in the UI/backend
    # even when users embed an activity from an external source (since these values are needed for totals calculations).
    page.fill('input[name="oirl-mb-distance"]', "21.1")
    page.fill('input[name="oirl-mb-duration"]', "02:01:29")
    
    # Fill in Strava details
    page.locator("#oirl-mb-embed-external-strava").check()
    # Wait for input to be visible and fill it
    page.wait_for_selector("input[name='oirl-mb-strava-activity']")
    page.fill("input[name='oirl-mb-strava-activity']", strava_activity_id)
    
    # Publish post
    page.click("#publish")
    page.wait_for_selector(".notice-success, #message")
    
    # --- 2. Create Garmin Embed Post ---
    page.goto(f"{base_url}/wp-admin/post-new.php?post_type=oi_run_log_post")
    page.fill("#title", garmin_post_title)
    
    # Switch to Text/HTML view for post editor and fill content
    if page.locator("#content-html").is_visible():
        page.click("#content-html")
    page.fill("#content", f"This is an E2E test verifying Garmin embed.")
    
    # TODO: In the future, enforce setting Distance and Duration values in the UI/backend
    # even when users embed an activity from an external source (since these values are needed for totals calculations).
    page.fill('input[name="oirl-mb-distance"]', "42.2")
    page.fill('input[name="oirl-mb-duration"]', "03:54:53")
    
    # Fill in Garmin details
    page.locator("#oirl-mb-embed-external-garmin").check()
    # Wait for input to be visible and fill it
    page.wait_for_selector("input[name='oirl-mb-garmin-activity']")
    page.fill("input[name='oirl-mb-garmin-activity']", garmin_activity_id)
    
    # Publish post
    page.click("#publish")
    page.wait_for_selector(".notice-success, #message")
    
    # --- 3. Verify Strava Embed Frontend ---
    page.goto(f"{base_url}/")
    expect(page.locator("body")).to_contain_text(strava_post_title)
    page.locator(f"a:has-text('{strava_post_title}')").first.click()
    page.wait_for_url(lambda url: "oi_run_log_post" in url)
    
    # Verify the Strava link and image are injected correctly
    strava_link = page.locator(f"a[href*='strava.com/activities/{strava_activity_id}']")
    expect(strava_link).to_be_visible()
    strava_img = strava_link.locator("img")
    expect(strava_img).to_have_attribute("src", re.compile(rf"activities/{strava_activity_id}\.jpeg"))
    
    # --- 4. Verify Garmin Embed Frontend ---
    page.goto(f"{base_url}/")
    expect(page.locator("body")).to_contain_text(garmin_post_title)
    page.locator(f"a:has-text('{garmin_post_title}')").first.click()
    page.wait_for_url(lambda url: "oi_run_log_post" in url)
    
    # Verify the Garmin iframe and link are injected correctly
    garmin_iframe = page.locator(f"iframe[src*='garmin.com/app/activity/embed/{garmin_activity_id}']")
    expect(garmin_iframe).to_be_attached()
    
    garmin_link = page.locator(f"a[href*='garmin.com/activity/{garmin_activity_id}']")
    expect(garmin_link).to_be_visible()
    expect(garmin_link).to_contain_text("View activity on Garmin Connect")
