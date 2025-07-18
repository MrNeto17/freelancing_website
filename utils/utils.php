
<?php 

function getDefaultUsername(string $email): string {
    $parts = explode('@', $email);
    return $parts[0];
}


function dateFormat(int $datetime): string {
    $timestamp = new DateTime(date('Y-m-d h:i:s', $datetime));
    $current = new DateTime(date('Y-m-d h:i:s'));
    $timediff = $current->diff($timestamp);

    if ($timediff->y > 0) {
        return $timediff->y === 1 ? $timediff->y . " year" : $timediff->y . " years";
    } elseif ($timediff->m > 0) {
        return $timediff->m === 1 ? $timediff->m . " month" : $timediff->m . " months";
    } elseif ($timediff->d > 0) {
        return $timediff->d === 1 ? $timediff->d . " day" : $timediff->d . " days";
    } elseif ($timediff->h > 0) {
        return $timediff->h === 1 ? $timediff->h . " hour" : $timediff->h . " hours";
    } elseif ($timediff->i > 0) {
        return $timediff->i === 1 ? $timediff->i . " minute" : $timediff->i . " minutes";
    } else {
        return "Just now";
    }
}


function sanitizeText(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function sanitizeNullableText(?string $input): ?string {
    if ($input === null) return null;
    $input = trim($input);
    return $input === '' ? null : htmlspecialchars($input, ENT_QUOTES, 'UTF-8');
}

function is_email(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function is_username(string $username): bool {
    return preg_match('/^[a-zA-Z0-9_-]{3,20}$/', $username) === 1;
}

function is_slug(string $slug): bool {
    return preg_match('/^[a-z0-9-]+$/', $slug) === 1;
}

function is_hex_token(string $token, int $length = 64): bool {
    return preg_match('/^[a-f0-9]{' . $length . '}$/i', $token) === 1;
}

function is_postal_code(string $zip): bool {
    return preg_match('/^\d{5}(-\d{4})?$/', $zip) === 1;
}

function is_phone_number(string $phone): bool {
    return preg_match('/^\+?[0-9]{9,15}$/', $phone) === 1;
}

function is_currency_amount(string $amount): bool {
    return preg_match('/^\d+(\.\d{2})?$/', $amount) === 1;
}

function is_alphanumeric(string $input, bool $allow_spaces = false): bool {
    $pattern = $allow_spaces ? '/^[a-zA-Z0-9 ]+$/' : '/^[a-zA-Z0-9]+$/';
    return preg_match($pattern, $input) === 1;
}

function is_password_complex(string $password): bool {
    return preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password) === 1;
}



function convertToBaseCurrency($amount, $currency) {
    $rates = [
        'USD' => 1,
        'EUR' => 1.1,
        'GBP' => 1.25,
        'JPY' => 0.007,
    ];
    return isset($rates[$currency]) ? $amount * $rates[$currency] : $amount;
}

?>