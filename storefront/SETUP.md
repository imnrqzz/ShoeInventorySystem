# SMTP Email Verification Setup

Before registration email verification can be used in the storefront, you must perform a one-time Google SMTP OAuth credential configuration.

## Setup Steps

1. Open your terminal at the root of the project.
2. Run the CLI setup script:
   ```bash
   php storefront/scripts/setup_smtp_oauth.php
   ```
3. Copy the URL printed in the terminal, open it in your web browser, and sign in with the Gmail account you want to send verification emails from.
4. Once authorized, the callback page in your browser will display the variables to add to your environment.
5. Open your `storefront/.env` file and append the following keys:
   ```env
   GMAIL_SMTP_USER=your-sending-email@gmail.com
   GMAIL_SMTP_REFRESH_TOKEN=copied_refresh_token
   ```
6. **Clean Up**: For security, delete the standalone callback file `storefront/oauth-smtp-callback.php` from your web server directory once the setup is complete.
