<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Customer.php';

class AuthController
{
    private array $oauth;
    private ?string $lastSmtpError = null;

    public function __construct()
    {
        $this->oauth = require __DIR__ . '/../config/oauth_config.php';
    }

    public function showRegister(): void
    {
        $this->render('auth/register', [
            'error'        => flash_get('register_error'),
            'oauthEnabled' => $this->oauth['enabled'] && $this->oauthConfigured(),
        ]);
    }

    public function register(): void
    {
        verify_csrf();

        $pdo      = Database::getConnection();
        $name     = trim($_POST['name'] ?? '');
        // Reconstruct full shipping address from detailed fields
        $houseNumber = trim($_POST['house_number'] ?? '');
        $barangay    = trim($_POST['barangay'] ?? '');
        $city        = trim($_POST['city'] ?? '');
        $province    = trim($_POST['province'] ?? '');
        $region      = trim($_POST['region'] ?? '');
        $country     = trim($_POST['country'] ?? '');
        $postalCode  = trim($_POST['postal_code'] ?? '');

        if ($houseNumber !== '' && $barangay !== '' && $city !== '' && $province !== '' && $region !== '' && $country !== '' && $postalCode !== '') {
            $address = "{$houseNumber}, {$barangay}, {$city}, {$province}, {$region}, {$country}, {$postalCode}";
        } else {
            $address = '';
        }
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm  = $_POST['confirm_password'] ?? '';

        if ($name === '' || $address === '' || $email === '' || $password === '') {
            flash_set('register_error', 'Please fill in all fields (Name, Address, Email, and Password).');
            $this->redirect('register');
            return;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash_set('register_error', 'Please enter a valid email address.');
            $this->redirect('register');
            return;
        }
        // Explicit Rate Limiting threshold check (5 attempts in 15 minutes)
        if (is_rate_limited($pdo, $email, 5, 15)) {
            flash_set('register_error', 'Too many registration attempts. Locked out. Please wait 15 minutes.');
            $this->redirect('register');
            return;
        }
        if (strlen($password) < 8) {
            record_login_attempt($pdo, $email);
            flash_set('register_error', 'Password must be at least 8 characters.');
            $this->redirect('register');
            return;
        }
        if ($password !== $confirm) {
            record_login_attempt($pdo, $email);
            flash_set('register_error', 'Passwords do not match.');
            $this->redirect('register');
            return;
        }
        if (Customer::findByEmail($email)) {
            record_login_attempt($pdo, $email);
            flash_set('register_error', 'An account with that email already exists.');
            $this->redirect('register');
            return;
        }

        // Generate 6-digit numeric email verification code
        $verificationCode = strval(rand(100000, 999999));
        $hashedCode = password_hash($verificationCode, PASSWORD_DEFAULT);

        $customerId = Customer::createLocal($name, $email, $password, $address, $hashedCode);
        if (!$customerId) {
            flash_set('register_error', 'Could not create account. Please try again.');
            $this->redirect('register');
            return;
        }

        clear_old_attempts($pdo);

        // Store email temporarily in session to identify the user for code verification
        $_SESSION['unverified_email'] = $email;

        // Attempt actual SMTP send
        if ($this->sendVerificationEmail($email, $verificationCode)) {
            flash_set('verify_code_info', 'A 6-digit confirmation code has been sent to ' . htmlspecialchars($email) . '. Please check your inbox (and spam folder) to verify your account.');
        } else {
            flash_set('verify_code_error', "We couldn't send your verification email. DEBUG ERROR: " . htmlspecialchars($this->lastSmtpError ?? 'Unknown error'));
        }
        
        $this->redirect('verify-code');
    }

    public function showVerifyCode(): void
    {
        if (empty($_SESSION['unverified_email'])) {
            $this->redirect('register');
            return;
        }
        $this->render('auth/verify_code', [
            'email' => $_SESSION['unverified_email'],
            'info'  => flash_get('verify_code_info'),
            'error' => flash_get('verify_code_error')
        ]);
    }

    public function verifyCode(): void
    {
        verify_csrf();

        if (empty($_SESSION['unverified_email'])) {
            $this->redirect('register');
            return;
        }

        $email = $_SESSION['unverified_email'];
        $code  = trim($_POST['code'] ?? '');

        if ($code === '') {
            flash_set('verify_code_error', 'Please enter the 6-digit verification code.');
            $this->redirect('verify-code');
            return;
        }

        if (Customer::verifyCode($email, $code)) {
            unset($_SESSION['unverified_email']);
            flash_set('login_error', 'Email verified successfully! You can now log in.');
            $this->redirect('login');
        } else {
            flash_set('verify_code_error', 'Invalid or incorrect verification code. Please check your inbox and try again.');
            $this->redirect('verify-code');
        }
    }

    public function resendCode(): void
    {
        verify_csrf();

        if (empty($_SESSION['unverified_email'])) {
            $this->redirect('register');
            return;
        }

        $email = $_SESSION['unverified_email'];
        $pdo = Database::getConnection();

        if ($this->isResendRateLimited($pdo, $email)) {
            flash_set('verify_code_error', 'Too many resend attempts. Please wait before trying again.');
            $this->redirect('verify-code');
            return;
        }

        $this->recordResendAttempt($pdo, $email);

        $verificationCode = strval(rand(100000, 999999));
        $hashedCode = password_hash($verificationCode, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('UPDATE customers SET verification_code = ? WHERE email = ? AND is_verified = 0');
        $stmt->execute([$hashedCode, $email]);

        if ($this->sendVerificationEmail($email, $verificationCode)) {
            flash_set('verify_code_info', 'A new 6-digit confirmation code has been sent to ' . htmlspecialchars($email) . '. Please check your inbox (and spam folder).');
        } else {
            flash_set('verify_code_error', "We couldn't send your verification email — please try again or contact support.");
        }

        $this->redirect('verify-code');
    }

    public function showLogin(): void
    {
        $this->render('auth/login', [
            'error'        => flash_get('login_error'),
            'oauthEnabled' => $this->oauth['enabled'] && $this->oauthConfigured(),
        ]);
    }

    public function login(): void
    {
        verify_csrf();

        $pdo      = Database::getConnection();
        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($email === '' || $password === '') {
            flash_set('login_error', 'Please enter your email and password.');
            $this->redirect('login');
            return;
        }

        // Explicit Rate Limiting threshold check (5 attempts in 15 minutes)
        if (is_rate_limited($pdo, $email, 5, 15)) {
            flash_set('login_error', 'Too many failed attempts. Locked out. Please wait 15 minutes.');
            $this->redirect('login');
            return;
        }

        $customer = Customer::findByEmail($email);
        if (!$customer || !Customer::verifyPassword($customer, $password)) {
            record_login_attempt($pdo, $email);
            flash_set('login_error', 'Incorrect email or password.');
            $this->redirect('login');
            return;
        }

        // Block login if the email is not verified
        if (isset($customer['is_verified']) && (int)$customer['is_verified'] === 0) {
            flash_set('login_error', 'Your email is not verified. Please verify it via the confirmation link sent to your inbox.');
            $this->redirect('login');
            return;
        }

        clear_old_attempts($pdo);
        $this->logCustomerIn($customer);
        $this->redirect('home');
    }

    public function logout(): void
    {
        unset($_SESSION['customer']);
        session_regenerate_id(true);
        $this->redirect('home');
    }

    public function redirectToGoogle(): void
    {
        if (!$this->oauth['enabled'] || empty($this->oauth['google']['client_id'])) {
            flash_set('login_error', 'Google sign-in is not configured.');
            $this->redirect('login');
            return;
        }

        $cfg = $this->oauth['google'];
        $_SESSION['oauth_state'] = bin2hex(random_bytes(16));

        $params = [
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $this->oauth['base_url'] . $cfg['redirect_path'],
            'response_type' => 'code',
            'scope'         => $cfg['scope'],
            'state'         => $_SESSION['oauth_state'],
            'access_type'   => 'online',
            'prompt'        => 'select_account',
        ];

        header('Location: ' . $cfg['auth_url'] . '?' . http_build_query($params));
        exit;
    }

    public function googleCallback(): void
    {
        $cfg = $this->oauth['google'];

        // Standard Google Login Callback
        if (!$this->stateIsValid()) {
            flash_set('login_error', 'Login session expired, please try again.');
            $this->redirect('login');
            return;
        }

        $code = $_GET['code'] ?? null;
        if (!$code) {
            flash_set('login_error', 'Google sign-in was cancelled.');
            $this->redirect('login');
            return;
        }

        $tokenResponse = $this->httpPostForm($cfg['token_url'], [
            'client_id'     => $cfg['client_id'],
            'client_secret' => $cfg['client_secret'],
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $this->oauth['base_url'] . $cfg['redirect_path'],
        ]);

        if (empty($tokenResponse['access_token'])) {
            flash_set('login_error', 'Could not authenticate with Google. Check redirect URI registration.');
            $this->redirect('login');
            return;
        }

        $profile = $this->httpGet($cfg['userinfo_url'], [
            'Authorization: Bearer ' . $tokenResponse['access_token'],
        ]);

        if (empty($profile['email'])) {
            flash_set('login_error', 'Google did not return an email address.');
            $this->redirect('login');
            return;
        }

        $customer = Customer::findOrCreateFromOAuth(
            'google',
            $profile['sub'] ?? $profile['id'] ?? $profile['email'],
            $profile['name'] ?? 'Google User',
            $profile['email'],
            $profile['picture'] ?? null
        );

        if (!$customer) {
            flash_set('login_error', 'Could not create account.');
            $this->redirect('login');
            return;
        }

        $this->logCustomerIn($customer);
        $this->redirect('home');
    }

    public function redirectToFacebook(): void
    {
        if (!$this->oauth['enabled'] || empty($this->oauth['facebook']['app_id'])) {
            flash_set('login_error', 'Facebook sign-in is not configured.');
            $this->redirect('login');
            return;
        }

        $cfg = $this->oauth['facebook'];
        $_SESSION['oauth_state'] = bin2hex(random_bytes(16));

        $params = [
            'client_id'     => $cfg['app_id'],
            'redirect_uri'  => $this->oauth['base_url'] . $cfg['redirect_path'],
            'response_type' => 'code',
            'scope'         => $cfg['scope'],
            'state'         => $_SESSION['oauth_state'],
        ];

        header('Location: ' . $cfg['auth_url'] . '?' . http_build_query($params));
        exit;
    }

    public function facebookCallback(): void
    {
        $cfg = $this->oauth['facebook'];

        if (!$this->stateIsValid()) {
            flash_set('login_error', 'Login session expired, please try again.');
            $this->redirect('login');
            return;
        }

        $code = $_GET['code'] ?? null;
        if (!$code) {
            flash_set('login_error', 'Facebook sign-in was cancelled.');
            $this->redirect('login');
            return;
        }

        $tokenResponse = $this->httpGet($cfg['token_url'], [], [
            'client_id'     => $cfg['app_id'],
            'client_secret' => $cfg['app_secret'],
            'code'          => $code,
            'redirect_uri'  => $this->oauth['base_url'] . $cfg['redirect_path'],
        ]);

        if (empty($tokenResponse['access_token'])) {
            flash_set('login_error', 'Could not authenticate with Facebook. Check redirect URI registration.');
            $this->redirect('login');
            return;
        }

        $profile = $this->httpGet($cfg['userinfo_url'], [], [
            'fields'       => 'id,name,email,picture',
            'access_token' => $tokenResponse['access_token'],
        ]);

        if (empty($profile['email'])) {
            flash_set('login_error', 'Facebook did not return an email address for this account.');
            $this->redirect('login');
            return;
        }

        $avatar = $profile['picture']['data']['url'] ?? null;

        $customer = Customer::findOrCreateFromOAuth(
            'facebook',
            $profile['id'],
            $profile['name'] ?? 'Facebook User',
            $profile['email'],
            $avatar
        );

        if (!$customer) {
            flash_set('login_error', 'Could not create account.');
            $this->redirect('login');
            return;
        }

        $this->logCustomerIn($customer);
        $this->redirect('home');
    }

    private function oauthConfigured(): bool
    {
        return !empty($this->oauth['google']['client_id']) || !empty($this->oauth['facebook']['app_id']);
    }

    private function stateIsValid(): bool
    {
        $valid = isset($_GET['state'], $_SESSION['oauth_state'])
            && hash_equals($_SESSION['oauth_state'], $_GET['state']);
        unset($_SESSION['oauth_state']);
        return $valid;
    }

    private function logCustomerIn(?array $customer): void
    {
        if (!$customer) {
            return;
        }

        $_SESSION['customer'] = [
            'id'     => (int) $customer['id'],
            'name'   => $customer['name'],
            'email'  => $customer['email'],
            'avatar' => $customer['avatar'] ?? null,
        ];
        session_regenerate_id(true);
    }

    private function redirect(string $page): void
    {
        header('Location: ' . STOREFRONT_BASE . '/index.php?page=' . urlencode($page));
        exit;
    }

    private function httpPostForm(string $url, array $fields): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => http_build_query($fields),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function httpGet(string $url, array $headers = [], array $queryParams = []): array
    {
        if (!empty($queryParams)) {
            $url .= (str_contains($url, '?') ? '&' : '?') . http_build_query($queryParams);
        }

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_TIMEOUT        => 15,
        ]);
        $response = curl_exec($ch);
        curl_close($ch);

        $decoded = json_decode((string) $response, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function render(string $view, array $data = []): void
    {
        extract($data);
        require __DIR__ . '/../views/' . $view . '.view.php';
    }

    private function getSmtpAccessToken(): ?string
    {
        $clientId = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
        $clientSecret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';
        $refreshToken = $_ENV['GMAIL_SMTP_REFRESH_TOKEN'] ?? '';

        if (empty($clientId) || empty($clientSecret) || empty($refreshToken)) {
            return null;
        }

        $cfg = $this->oauth['google'];
        $response = $this->httpPostForm($cfg['token_url'], [
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        return $response['access_token'] ?? null;
    }

    private function sendVerificationEmail(string $email, string $code): bool
    {
        $smtpUser = $_ENV['GMAIL_SMTP_USER'] ?? '';
        $accessToken = $this->getSmtpAccessToken();

        if (empty($smtpUser) || !$accessToken) {
            $reason = empty($smtpUser) ? 'Missing GMAIL_SMTP_USER in .env' : 'Failed to fetch fresh access token using GMAIL_SMTP_REFRESH_TOKEN';
            $this->lastSmtpError = $reason;
            error_log("SMTP Send Failed: " . $reason);
            echo "<pre style='background:#f8d7da;color:#721c24;padding:15px;border:1px solid #f5c6cb;margin:20px;border-radius:4px;'>DEBUG ERROR: " . htmlspecialchars($reason) . "</pre>"; // TEMPORARY - remove after debugging
            return false;
        }

        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->SMTPDebug = \PHPMailer\PHPMailer\SMTP::DEBUG_OFF;
            $mail->Host = 'smtp.gmail.com';
            $mail->Port = 587;
            $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->SMTPAuth = true;
            $mail->AuthType = 'XOAUTH2';

            $mail->setOAuth(new CustomSmtpOAuth($smtpUser, $accessToken));

            $mail->setFrom($smtpUser, 'SOLEHAUS');
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = 'Verify Your SoleHaus Account';

            // HTML Body
            $mail->Body = "
                <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eee;'>
                    <h2 style='color: #111;'>Verify Your SoleHaus Account</h2>
                    <p>Thank you for registering at SoleHaus. Please verify your email using the following 6-digit confirmation code:</p>
                    <div style='background: #f4f4f4; padding: 15px; text-align: center; font-size: 2rem; font-weight: bold; letter-spacing: 4px; border-radius: 4px; margin: 20px 0;'>
                        {$code}
                    </div>
                    <p>If you did not request this, you can safely ignore this email.</p>
                </div>
            ";

            $mail->send();
            return true;
        } catch (\Exception $e) {
            $err = $e->getMessage();
            // Securely strip any 6-digit code from SMTP logs to prevent leak
            $errFiltered = preg_replace('/\b\d{6}\b/', '[REDACTED_CODE]', $err);
            $this->lastSmtpError = $errFiltered;
            error_log("PHPMailer SMTP error: " . $errFiltered);
            echo "<pre style='background:#f8d7da;color:#721c24;padding:15px;border:1px solid #f5c6cb;margin:20px;border-radius:4px;'>DEBUG ERROR: " . htmlspecialchars($errFiltered) . "</pre>"; // TEMPORARY - remove after debugging
            return false;
        }
    }

    private function isResendRateLimited(\PDO $pdo, string $email): bool
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // 1. Check 60 seconds cooldown (1 attempt max)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM customer_login_attempts 
            WHERE (email = ? OR ip_address = ?) 
            AND attempted_at > DATE_SUB(NOW(), INTERVAL 1 MINUTE)
        ");
        $stmt->execute(["resend:$email", "resend_ip:$ip"]);
        if ((int)$stmt->fetchColumn() >= 1) {
            return true;
        }

        // 2. Check 1 hour window (5 attempts max)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM customer_login_attempts 
            WHERE (email = ? OR ip_address = ?) 
            AND attempted_at > DATE_SUB(NOW(), INTERVAL 1 HOUR)
        ");
        $stmt->execute(["resend:$email", "resend_ip:$ip"]);
        if ((int)$stmt->fetchColumn() >= 5) {
            return true;
        }

        return false;
    }

    private function recordResendAttempt(\PDO $pdo, string $email): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $stmt = $pdo->prepare("INSERT INTO customer_login_attempts (email, ip_address) VALUES (?, ?)");
        $stmt->execute(["resend:$email", "resend_ip:$ip"]);
    }
}

/**
 * Custom OAuth token provider for PHPMailer to allow direct injection of retrieved access tokens.
 */
class CustomSmtpOAuth implements \PHPMailer\PHPMailer\OAuthTokenProvider
{
    private string $email;
    private string $token;

    public function __construct(string $email, string $token)
    {
        $this->email = $email;
        $this->token = $token;
    }

    public function getOauth64(): string
    {
        return base64_encode("user=" . $this->email . "\001auth=Bearer " . $this->token . "\001\001");
    }
}

