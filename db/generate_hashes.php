<?php

$accounts = [
    'admin'        => 'admin123',
    'clerk1'       => 'clerk123',
    'loanofficer1' => 'loan123',
    'juan (member)'=> 'member123',
    'maria (applicant)' => 'applicant123',
];

echo '<pre style="font-family:monospace;font-size:13px;padding:24px;background:#1e1e2e;color:#cdd6f4;">';
echo "Copy these hashes into seed.sql\n\n";
foreach ($accounts as $user => $pw) {
    $hash = password_hash($pw, PASSWORD_DEFAULT);
    echo "$user / $pw\n  => $hash\n\n";
}
echo '</pre>';
?>
