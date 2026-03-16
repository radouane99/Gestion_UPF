<?php
// includes/EmailService.php - Version Moderne et Élégante
require_once __DIR__ . '/../PHPMailer/src/Exception.php';
require_once __DIR__ . '/../PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../PHPMailer/src/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $mail;
    private $config;
    /**
     * Constructeur - Initialise la configuration
     */
    public function __construct() {
        $this->mail = new PHPMailer(true);
        $this->loadConfiguration();
    }
    
    /**
     * Charge la configuration depuis un fichier ou variables d'environnement
     */
    private function loadConfiguration() {
        // Idéalement, ces valeurs viendraient d'un fichier .env
        $this->config = [
            'host' => 'smtp.gmail.com',
            'port' => 587,
            'secure' => 'tls',
            'username' => 'aniss.massawi42@gmail.com',
            'password' => 'fsoi pkgy niay lcqg',
            'from_email' => 'aniss.massawi42@gmail.com',
            'from_name' => 'UPF Gestion',
            'debug' => false // Mettre à true en développement
        ];
    }
    /**
     * Configure le serveur SMTP
     */
    private function setupServer(): void {
        try {
            // Configuration SMTP
            $this->mail->isSMTP();
            $this->mail->Host = $this->config['host'];
            $this->mail->SMTPAuth = true;
            $this->mail->Username = $this->config['username'];
            $this->mail->Password = $this->config['password'];
            $this->mail->SMTPSecure = $this->config['secure'];
            $this->mail->Port = $this->config['port'];
            
            // Encodage
            $this->mail->CharSet = 'UTF-8';
            $this->mail->Encoding = 'base64';
            
            // Debug (optionnel)
            if ($this->config['debug']) {
                $this->mail->SMTPDebug = SMTP::DEBUG_SERVER;
            }
            
            // Timeout
            $this->mail->Timeout = 30;
            
        } catch (Exception $e) {
            throw new Exception("Erreur configuration SMTP: " . $e->getMessage());
        }
    }
    
    /**
     * Prépare l'email avec les paramètres communs
     */
    private function prepareEmail(string $to, string $name, string $subject, string $body, string $altBody = ''): void {
        // Expéditeur
        $this->mail->setFrom($this->config['from_email'], $this->config['from_name']);
        
        // Destinataire
        $this->mail->addAddress($to, $name);
        
        // Répondre à
        $this->mail->addReplyTo($this->config['from_email'], $this->config['from_name']);
        
        // Sujet
        $this->mail->Subject = $subject;
        
        // Corps HTML
        $this->mail->isHTML(true);
        $this->mail->Body = $body;
        
        // Corps texte alternatif
        $this->mail->AltBody = !empty($altBody) ? $altBody : strip_tags($body);
    }
    
    /**
     * Envoie un email
     */
    public function send(array $data): array {
        try {
            $this->setupServer();
            
            $this->prepareEmail(
                $data['to'],
                $data['name'],
                $data['subject'],
                $data['body'],
                $data['altBody'] ?? ''
            );
            
            // Ajouter des pièces jointes si nécessaire
            if (isset($data['attachments']) && is_array($data['attachments'])) {
                foreach ($data['attachments'] as $attachment) {
                    if (file_exists($attachment)) {
                        $this->mail->addAttachment($attachment);
                    }
                }
            }
            
            // Envoyer
            $this->mail->send();
            
            // Log de succès
            $this->log("Email envoyé avec succès à {$data['to']}");
            
            return [
                'success' => true,
                'message' => 'Email envoyé avec succès',
                'recipient' => $data['to']
            ];
            
        } catch (Exception $e) {
            // Log d'erreur
            $this->log("Erreur envoi email: " . $e->getMessage(), 'ERROR');
            
            return [
                'success' => false,
                'message' => "Erreur d'envoi: " . $this->mail->ErrorInfo,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Envoie un email de réinitialisation de mot de passe
     */
    public function sendPasswordResetEmail(string $to, string $name, string $token): array {
        $resetLink = "http://localhost/Gestion_UPF/reset_password.php?token=" . $token;
        
        $subject = "🔐 Réinitialisation de votre mot de passe - UPF Gestion";
        
        $body = $this->getPasswordResetTemplate($name, $resetLink);
        $altBody = $this->getPasswordResetTextTemplate($name, $resetLink);
        
        return $this->send([
            'to' => $to,
            'name' => $name,
            'subject' => $subject,
            'body' => $body,
            'altBody' => $altBody
        ]);
    }
    
    /**
     * Envoie un email de bienvenue avec identifiants
     */
    public function sendWelcomeEmail(string $to, string $name, string $login, string $password): array {
        $loginLink = "http://localhost/Gestion_UPF/login.php";
        
        $subject = "🎓 Bienvenue sur UPF Gestion - Vos identifiants";
        
        $body = $this->getWelcomeTemplate($name, $login, $password, $loginLink);
        $altBody = $this->getWelcomeTextTemplate($name, $login, $password, $loginLink);
        
        return $this->send([
            'to' => $to,
            'name' => $name,
            'subject' => $subject,
            'body' => $body,
            'altBody' => $altBody
        ]);
    }
    
    /**
     * Template HTML pour réinitialisation de mot de passe
     */
    private function getPasswordResetTemplate(string $name, string $resetLink): string {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Réinitialisation mot de passe</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Arial, sans-serif; background-color: #f4f4f4;">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 20px auto; background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <!-- Header avec gradient UPF -->
                <tr>
                    <td style="background: linear-gradient(135deg, #294898, #C72C82); padding: 40px 30px; text-align: center;">
                        <h1 style="color: white; margin: 0; font-size: 28px;">🔐 UPF Gestion</h1>
                        <p style="color: rgba(255,255,255,0.9); margin: 10px 0 0;">Université Privée de Fès</p>
                    </td>
                </tr>
                
                <!-- Contenu principal -->
                <tr>
                    <td style="padding: 40px 30px;">
                        <h2 style="color: #294898; margin-top: 0;">Bonjour ' . htmlspecialchars($name) . ',</h2>
                        
                        <p style="color: #333; line-height: 1.6;">Nous avons reçu une demande de réinitialisation de votre mot de passe.</p>
                        
                        <p style="color: #333; line-height: 1.6;">Cliquez sur le bouton ci-dessous pour choisir un nouveau mot de passe :</p>
                        
                        <!-- Bouton de réinitialisation -->
                        <table width="100%" cellpadding="0" cellspacing="0">
                            <tr>
                                <td align="center" style="padding: 30px 0;">
                                    <a href="' . $resetLink . '" style="display: inline-block; padding: 15px 40px; background: linear-gradient(135deg, #294898, #C72C82); color: white; text-decoration: none; border-radius: 50px; font-weight: bold; box-shadow: 0 5px 15px rgba(199,44,130,0.3);">🔐 Réinitialiser mon mot de passe</a>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Lien de secours -->
                        <p style="color: #666; font-size: 14px; line-height: 1.6;">Si le bouton ne fonctionne pas, copiez ce lien dans votre navigateur :</p>
                        <p style="background: #f5f5f5; padding: 15px; border-radius: 8px; font-family: monospace; word-break: break-all; font-size: 13px;">
                            <a href="' . $resetLink . '" style="color: #C72C82;">' . $resetLink . '</a>
                        </p>
                        
                        <!-- Avertissement de sécurité -->
                        <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 30px; background: #fff3cd; border-radius: 10px;">
                            <tr>
                                <td style="padding: 20px;">
                                    <p style="margin: 0; color: #856404;">
                                        <strong>⚠️ Sécurité :</strong><br>
                                        • Ce lien expire dans 1 heure<br>
                                        • Si vous n\'avez pas demandé cette réinitialisation, ignorez cet email<br>
                                        • Ne partagez jamais ce lien avec personne
                                    </p>
                                </td>
                            </tr>
                        </table>
                        
                        <!-- Séparateur -->
                        <hr style="border: none; border-top: 1px solid #e0e0e0; margin: 30px 0;">
                        
                        <!-- Pied de page -->
                        <p style="color: #999; font-size: 13px; text-align: center; margin: 0;">
                            Cet email a été envoyé automatiquement par l\'application UPF Gestion.<br>
                            Université Privée de Fès - Tous droits réservés © ' . date('Y') . '
                        </p>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';
    }
    
    /**
     * Template texte pour réinitialisation (version sans HTML)
     */
    private function getPasswordResetTextTemplate(string $name, string $resetLink): string {
        return "Bonjour $name,\n\n" .
               "Nous avons reçu une demande de réinitialisation de votre mot de passe.\n\n" .
               "Pour réinitialiser votre mot de passe, copiez ce lien dans votre navigateur :\n" .
               "$resetLink\n\n" .
               "Ce lien expire dans 1 heure.\n\n" .
               "Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.\n\n" .
               "Cordialement,\n" .
               "UPF Gestion";
    }
    
    /**
     * Template HTML pour email de bienvenue
     */
    private function getWelcomeTemplate(string $name, string $login, string $password, string $loginLink): string {
        return '
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Bienvenue sur UPF Gestion</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: \'Segoe UI\', Arial, sans-serif; background-color: #f4f4f4;">
            <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 20px auto; background: white; border-radius: 20px; overflow: hidden;">
                <tr>
                    <td style="background: linear-gradient(135deg, #294898, #C72C82); padding: 40px 30px; text-align: center;">
                        <h1 style="color: white; margin: 0;">🎓 Bienvenue à l\'UPF</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 40px 30px;">
                        <h2 style="color: #294898;">Bonjour ' . htmlspecialchars($name) . ',</h2>
                        
                        <p>Votre compte a été créé sur l\'application de gestion UPF.</p>
                        
                        <!-- Identifiants -->
                        <table width="100%" style="background: #f5f5f5; border-radius: 10px; margin: 20px 0;">
                            <tr>
                                <td style="padding: 20px;">
                                    <p style="margin: 5px 0;"><strong>Login :</strong> ' . $login . '</p>
                                    <p style="margin: 5px 0;"><strong>Mot de passe :</strong> ' . $password . '</p>
                                </td>
                            </tr>
                        </table>
                        
                        <p style="text-align: center;">
                            <a href="' . $loginLink . '" style="display: inline-block; padding: 12px 30px; background: #C72C82; color: white; text-decoration: none; border-radius: 25px;">Se connecter</a>
                        </p>
                        
                        <p style="color: #856404; background: #fff3cd; padding: 15px; border-radius: 8px;">
                            <strong>⚠️ Important :</strong> Changez votre mot de passe après votre première connexion.
                        </p>
                    </td>
                </tr>
            </table>
        </body>
        </html>
        ';
    }
    
    /**
     * Template texte pour email de bienvenue
     */
    private function getWelcomeTextTemplate(string $name, string $login, string $password, string $loginLink): string {
        return "Bonjour $name,\n\n" .
               "Votre compte a été créé sur l'application UPF Gestion.\n\n" .
               "Vos identifiants :\n" .
               "Login : $login\n" .
               "Mot de passe : $password\n\n" .
               "Lien de connexion : $loginLink\n\n" .
               "Changez votre mot de passe après votre première connexion.\n\n" .
               "Cordialement,\n" .
               "UPF Gestion";
    }
    
    /**
     * Log les messages (à adapter selon ton système)
     */
    private function log(string $message, string $level = 'INFO'): void {
        $logMessage = date('Y-m-d H:i:s') . " [$level] $message" . PHP_EOL;
        $logFile = __DIR__ . '/../logs/email.log';
        
        // Créer le dossier logs s'il n'existe pas
        if (!file_exists(dirname($logFile))) {
            mkdir(dirname($logFile), 0777, true);
        }
        
        file_put_contents($logFile, $logMessage, FILE_APPEND);
    }
    
    /**
     * Vérifie la configuration
     */
    public function testConnection(): array {
        try {
            $this->setupServer();
            return [
                'success' => true,
                'message' => 'Configuration SMTP valide'
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
?>