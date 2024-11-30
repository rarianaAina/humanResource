<?php
require_once 'vendor/autoload.php'; // Charger SwiftMailer via Composer

// Configuration du transport SMTP
$transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
    ->setUsername('rarianamiadana@gmail.com')
    ->setPassword('mgxy pljh fskt zlbk')
    ->setStreamOptions([
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
            'allow_self_signed' => true
        ]
    ]);

// Création du mailer
$mailer = new Swift_Mailer($transport);

// Création du message
$message = (new Swift_Message('Test SwiftMailer'))
    ->setFrom(['rarianamiadana@gmail.com' => 'Ton Nom'])
    ->setTo(['rabhenintsoa@gmail.com' => 'Nom du destinataire'])
    ->setBody('Ceci est un test d\'envoi d\'email avec SwiftMailer.');

// Envoi du message
if ($mailer->send($message)) {
    echo 'L\'email a été envoyé avec succès.';
} else {
    echo 'Échec de l\'envoi de l\'email.';
}
