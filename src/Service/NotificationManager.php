<?php 
namespace App\Service;

/**
 * Notification manager.
 */
class NotificationManager {
	/**
	 * Retourne le contenu d'un template.
	 * @param string $templateName Nom de la template, avec ou sans chemin relatif depuis le dossier /templates. "/email/error" par défaut.
	 * @param array $parameters Tableau associatif pour passer des données au template. Liste vide par défaut.
	 * @return string
	 * @throws \App\Exception\FileNotFoundException
	 */
	static public function getTemplate(string $templateName = '/email/error', array &$parameters = []): string
	{
		// Cas template introuvable
		$fileName = $_SERVER['DOCUMENT_ROOT'] . '/../templates' . $templateName . '.html.php';
		if(!is_file($fileName)) {
			throw new \App\Exception\FileNotFoundException($fileName);
		}
		
		// Convertit la liste des paramètres en variables locales
		foreach($parameters as $key => &$value) {
			$key = '_' . $key;
			$$key = &$value;
		}
		
		// Charge le template et génère le contenu avec les variables locales
		ob_start();
		require $fileName;
		
		// Retourne le template généré
		return ob_get_clean();
	}
	
	/**
	 * Envoie un email de notification aux administrateurs en utilisant les templates dans "/templates/email/".
	 * @param string $templateName Nom de la template. "error" par défaut.
	 * @param array $parameters Tableau associatif pour passer des données au template. Liste vide par défaut.
	 * @param \App\Service\AbstractPdo $pdo (optionnel) pdo.
	 * @return bool
	 */
	static public function sendEmail(string $templateName = 'error', array &$parameters = [], ?\App\Service\AbstractPdo &$pdo = null): bool
	{
		// Cas destinataire unique (mot de passe oublié)
		if($templateName == 'documentation-password') {
			$to = $parameters['clientMail'];
		}
		
		// Cas notification automatique
		elseif($pdo !== null) {
			// Récupère la liste des administrateurs à notifier
			$administrators = $pdo->getResult('SELECT adm_administrator.*
				FROM public.adm_notification_type, public.adm_administrator, public.adm_administrator_notification
				WHERE adm_notification_type.notification_type_id = adm_administrator_notification.notification_type_id
				AND adm_administrator.administrator_id = adm_administrator_notification.administrator_id
				AND adm_notification_type.name = ?', 
				['templateName' => $templateName]);

			// Construit la liste des destinataires
			$to = [];
			foreach($administrators as &$administratorData) {
				$to[] = $administratorData['email'];
			}
			$to = implode(';', $to);
		}
		
		// Cas inconnu qui ne devrait jamais arriver
		else {
			throw new \App\Exception\EmailException('No recipient: PDO is required or "documentation-password" template in NotificatonManager::sendEmail.');		
		} 

		// Définit l'objet du mail
		$subjects = [
			'error' => 'erreur',
			'warning' => 'avertissement',
			'api-authentication' => 'tentative de connexion à l\'API',
			'administration-authentication' => 'tentative de connexion à l\'administration',
			'documentation-authentication' => 'tentative de connexion à la documentation',
			'integration-test-failed' => 'bug détecté lors de l\'integration continue',
			'owlink' => 'indisponibilité Owlink',
			'datamart' => 'indisponibilité Datamart',
			'documentation-contact' => 'documentation contact',
            'documentation-password' => 'nouveau mot de passe',
			'documentation-password-reset' => 'demande de réinitialisation du mot de passe'
		];
		$subject = mb_encode_mimeheader('Middleware : ' . $subjects[$templateName], 'UTF-8');
		
		// Récupère le sujet et le corps du message : $parameters['subject']
		$message = self::getTemplate('/email/' . $templateName, $parameters);
		
		// Cas environnement local
		if('.loc' == substr($_SERVER['SERVER_NAME'], -4)) {
			file_put_contents(
				$_SERVER['DOCUMENT_ROOT'] . '/../log/' . $templateName . '-mail-' . date('YmdHis') . '.txt',
				$message
			);
			return true;
		}
		
		// Définit les en-têtes du mail
		$headers = 'MIME-Version: 1.0'
			. "\r\n" . 'Content-type: text/html; charset=utf-8'
			. "\r\n" . 'From: Middleware <middleware-no-reply@apivia-courtage.fr>'
			. "\r\n" . 'X-Mailer: PHP/' . phpversion();
		
		// Cas autre environnement
		// Envoie le mail
		return mail($to, $subject, $message, $headers);
	}
}
?>