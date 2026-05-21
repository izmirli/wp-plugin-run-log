import http.server
import socketserver
import urllib.parse

PORT = 8080

class MockWordPressHandler(http.server.SimpleHTTPRequestHandler):
    # Store settings in memory
    settings = {
        "distance_unit": "km",
        "saved": False
    }

    def do_GET(self):
        url = urllib.parse.urlparse(self.path)
        
        if url.path == "/wp-login.php":
            self.send_response(200)
            self.send_header("Content-type", "text/html")
            self.end_headers()
            self.wfile.write(b"""
            <html>
            <body>
                <form method="POST" action="/wp-admin/">
                    <input type="text" id="user_login" name="log" />
                    <input type="password" id="user_pass" name="pwd" />
                    <input type="submit" id="wp-submit" value="Log In" />
                </form>
            </body>
            </html>
            """)
            
        elif url.path in ["/wp-admin/", "/wp-admin/edit.php"]:
            self.send_response(200)
            self.send_header("Content-type", "text/html")
            self.end_headers()
            
            # Show success message if saved in last action
            success_msg = ""
            if self.settings["saved"]:
                success_msg = '<div class="updated"><p><strong>Options saved</strong></p></div>'
                self.settings["saved"] = False # Reset flag for next view
            
            km_checked = "checked" if self.settings["distance_unit"] == "km" else ""
            mi_checked = "checked" if self.settings["distance_unit"] == "mi" else ""
            
            html = f"""
            <html>
            <body>
                <div class="wrap oirl">
                    <h3>Run Log Options</h3>
                    {success_msg}
                    <form method="POST" action="/wp-admin/edit.php?post_type=oi_run_log_post&page=oirl-options-menu">
                        <input type="radio" name="oirl-distance-unit" value="km" id="oirl-distance-unit-km" {km_checked}>
                        <label for="oirl-distance-unit-km">km</label>
                        
                        <input type="radio" name="oirl-distance-unit" value="mi" id="oirl-distance-unit-mi" {mi_checked}>
                        <label for="oirl-distance-unit-mi">mi</label>
                        
                        <input type="submit" name="Submit" class="button-primary" value="Save Changes">
                    </form>
                </div>
            </body>
            </html>
            """
            self.wfile.write(html.encode("utf-8"))
        else:
            self.send_response(404)
            self.end_headers()

    def do_POST(self):
        url = urllib.parse.urlparse(self.path)
        content_length = int(self.headers['Content-Length'])
        post_data = self.rfile.read(content_length).decode('utf-8')
        params = urllib.parse.parse_qs(post_data)
        
        if url.path == "/wp-login.php" or "/wp-admin/" in url.path:
            if "oirl-distance-unit" in params:
                self.settings["distance_unit"] = params["oirl-distance-unit"][0]
                self.settings["saved"] = True
            
            # Redirect to the options page to simulate POST-redirect-GET pattern
            self.send_response(303)
            self.send_header("Location", "/wp-admin/edit.php?post_type=oi_run_log_post&page=oirl-options-menu")
            self.end_headers()
        else:
            self.send_response(404)
            self.end_headers()

def run_server():
    handler = MockWordPressHandler
    with socketserver.TCPServer(("", PORT), handler) as httpd:
        print(f"Mock server running on port {PORT}")
        httpd.serve_forever()

if __name__ == "__main__":
    run_server()
