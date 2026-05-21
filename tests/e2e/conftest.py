import pytest

@pytest.fixture(scope="session")
def base_url(request):
    val = request.config.getoption("--base-url")
    if val:
        return val
    # Default to wp-env testing port
    return "http://localhost:8889"
