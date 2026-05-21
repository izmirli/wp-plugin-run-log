import pytest
from datetime import datetime
from playwright.sync_api import Page, expect

def test_run_taxonomies_linking(page: Page, base_url: str):
    # Go to login page
    page.goto(f"{base_url}/wp-login.php")
    
    # Check if we need to log in
    if page.locator("#user_login").is_visible():
        page.fill("#user_login", "admin")
        page.fill("#user_pass", "password")
        page.click("#wp-submit")
    
    # Wait for admin dashboard
    page.wait_for_url("**/wp-admin/**")
    
    # --- 1. Enable Goal and Gear links in Options if not already enabled ---
    page.goto(f"{base_url}/wp-admin/edit.php?post_type=oi_run_log_post&page=oirl-options-menu")
    goal_yes = page.locator("#oirl-goal-links-yes")
    gear_yes = page.locator("#oirl-gear-links-yes")
    if not goal_yes.is_checked() or not gear_yes.is_checked():
        goal_yes.check()
        gear_yes.check()
        page.click('input[name="Submit"]')
        expect(page.locator(".updated")).to_contain_text("Options saved")
    
    # Generate unique names with timestamps
    now = datetime.now()
    timestamp = now.strftime("%y%m%d%H%M%S")
    parent_goal_name = f"My Goal {timestamp}"
    sub_goal_name = f"Sub-goal {timestamp}"
    gear_name = f"My Shoes {timestamp}"
    post_title = f"Dynamic Taxonomy Run {timestamp}"
    
    # --- 2. Create unique Parent Goal ---
    page.goto(f"{base_url}/wp-admin/edit-tags.php?taxonomy=oi_goal_taxonomy&post_type=oi_run_log_post")
    page.fill("#tag-name", parent_goal_name)
    page.click("#submit")
    # Wait for AJAX to complete and verify name is in the list
    expect(page.locator("#the-list")).to_contain_text(parent_goal_name)
    
    # --- 3. Create unique Sub-Goal ---
    # Reload page to populate parent dropdown
    page.reload()
    page.fill("#tag-name", sub_goal_name)
    page.select_option("#parent", label=parent_goal_name)
    page.click("#submit")
    expect(page.locator("#the-list")).to_contain_text(sub_goal_name)
    
    # --- 4. Create unique Gear ---
    page.goto(f"{base_url}/wp-admin/edit-tags.php?taxonomy=oi_gear_taxonomy&post_type=oi_run_log_post")
    page.fill("#tag-name", gear_name)
    page.click("#submit")
    # Wait for AJAX to complete and verify name is in the list
    expect(page.locator("#the-list")).to_contain_text(gear_name)
    
    # --- 5. Create Run Log Post and link them ---
    page.goto(f"{base_url}/wp-admin/post-new.php?post_type=oi_run_log_post")
    page.fill("#title", post_title)
    
    # Switch to Text/HTML view for post editor and fill content
    if page.locator("#content-html").is_visible():
        page.click("#content-html")
    page.fill("#content", f"This is an E2E test verifying Goals and Gear linking.")
    
    # Fill run details
    page.fill('input[name="oirl-mb-distance"]', "15.0")
    page.fill('input[name="oirl-mb-duration"]', "01:15:00")
    page.fill('input[name="oirl-mb-elevation"]', "150")
    page.fill('input[name="oirl-mb-calories"]', "1050")
    
    # --- Assign Goal (Hierarchical taxonomy -> Checkbox) ---
    # We check only the sub-goal to test child selection in hierarchical categories
    sub_goal_checkbox = page.locator(f"label:has-text('{sub_goal_name}') input[type='checkbox']")
    sub_goal_checkbox.check()
    
    # --- Assign Gear (Non-hierarchical taxonomy -> Tag-style entry box) ---
    page.fill("#new-tag-oi_gear_taxonomy", gear_name)
    page.click("#tagsdiv-oi_gear_taxonomy input.tagadd")
    
    # Publish post
    page.click("#publish")
    page.wait_for_selector(".notice-success, #message")
    
    # --- 6. Verify Frontend Rendering ---
    page.goto(f"{base_url}/")
    
    # Confirm post title is visible on the home page feed
    expect(page.locator("body")).to_contain_text(post_title)
    
    # Navigate to the single post page by clicking the post title
    page.locator(f"a:has-text('{post_title}')").first.click()
    page.wait_for_url(lambda url: "oi_run_log_post" in url)
    
    # Verify taxonomy links are visible on the single post page bottom links div
    expect(page.locator("#oril_bottom_links")).to_contain_text(f"Goal: {sub_goal_name}")
    expect(page.locator("#oril_bottom_links")).to_contain_text(f"Gear: {gear_name}")
    
    # --- 7. Verify Gear Archive page aggregation ---
    # Click the Gear link to navigate to the gear taxonomy archive
    page.locator(f"a:has-text('{gear_name}')").click()
    
    # The gear archive page should show this post and the gear title
    expect(page.locator("h1.page-title, h1.archive-title, h1")).to_contain_text(gear_name)
    expect(page.locator("body")).to_contain_text(post_title)
