# Authenticating requests

To authenticate requests, include an **`Authorization`** header with the value **`"Bearer {TOKEN}"`**.

All authenticated endpoints are marked with a `requires authentication` badge in the documentation below.

Token diperoleh dari <code>POST /auth/two-factor</code> setelah login; berlaku 12 jam.
