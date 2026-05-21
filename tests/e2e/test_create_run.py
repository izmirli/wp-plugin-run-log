import pytest
from datetime import datetime
from playwright.sync_api import Page, expect

def test_create_run_log_post(page: Page, base_url: str):
    # Go to login page
    page.goto(f"{base_url}/wp-login.php")
    
    # Check if we need to log in
    if page.locator("#user_login").is_visible():
        page.fill("#user_login", "admin")
        page.fill("#user_pass", "password")
        page.click("#wp-submit")
    
    # Wait for admin dashboard
    page.wait_for_url("**/wp-admin/**")
    
    # Navigate to Create New Run Log page
    page.goto(f"{base_url}/wp-admin/post-new.php?post_type=oi_run_log_post")
    
    # Generate timestamp and date strings
    now = datetime.now()
    title_time = now.strftime("%Y-%m-%dT%H:%M:%S")
    
    day = str(now.day)
    month = now.strftime("%b")
    year = str(now.year)
    hour = str(now.hour)
    minute = now.strftime("%M")
    second = now.strftime("%S")
    body_time = f"{day}-{month}-{year} at {hour}:{minute}:{second}"
    
    post_title = f"E2E Test Run {title_time}"
    post_content = f"this is a test post created on {body_time}"
    
    # Fill in the Title
    page.fill("#title", post_title)
    
    # Fill in the regular text box of the post
    if page.locator("#content-html").is_visible():
        page.click("#content-html")
    page.fill("#content", post_content)
    
    # Fill in the metabox parameters
    page.fill('input[name="oirl-mb-distance"]', "10.0")
    page.fill('input[name="oirl-mb-duration"]', "00:50:00")
    page.fill('input[name="oirl-mb-elevation"]', "100")
    page.fill('input[name="oirl-mb-calories"]', "700")
    
    # Publish the post
    page.click("#publish")
    
    # Wait for the post to be saved
    page.wait_for_selector(".notice-success, #message")
    
    # Navigate to the front-end to verify
    page.goto(f"{base_url}/")
    
    # Verify our post is visible on the home page
    expect(page.locator("body")).to_contain_text(post_title)
    expect(page.locator("body")).to_contain_text(post_content)
    
    # Locate the first data box (which belongs to our newly created post at the top)
    first_data_box = page.locator(".oirl-data-box").first
    expect(first_data_box).to_be_visible()
    
    # Distance (10 km)
    expect(first_data_box).to_contain_text("10")
    expect(first_data_box).to_contain_text("km")
    
    # Duration (00:50:00)
    expect(first_data_box).to_contain_text("00:50:00")
    
    # Pace (10 km in 50 mins = 5:00 min/km)
    expect(first_data_box).to_contain_text("5:00")
    expect(first_data_box).to_contain_text("min/km")
