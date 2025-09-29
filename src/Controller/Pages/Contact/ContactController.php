<?php

declare(strict_types=1);

namespace App\Controller\Pages\Contact;

use App\Form\pages\contact\ContactType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\Form\FormInterface;

#[Route('/contact')]
final class ContactController extends AbstractController
{
    /** change-les si besoin */
    private const EMAIL_TO   = 'contact@getecoride.com';
    private const EMAIL_FROM = 'no-reply@getecoride.com';

    #[Route('', name: 'contact_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $form = $this->createForm(ContactType::class);

        return $this->render('pages/contact/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/submit', name: 'contact_submit', methods: ['POST'])]
    public function submit(Request $request, MailerInterface $mailer): JsonResponse
    {
        // Facultatif : garantir la soumission AJAX
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['ok' => false, 'error' => 'Soumission invalide.'], 400);
        }

        $form = $this->createForm(ContactType::class);
        // handleRequest() fonctionne avec FormData (nommés contact[...])
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new JsonResponse(['ok' => false, 'error' => 'Formulaire non soumis.'], 400);
        }

        if (!$form->isValid()) {
            return new JsonResponse([
                'ok'     => false,
                'errors' => $this->collectErrors($form),
            ], 422);
        }

        $data = $form->getData(); // ['fullName','email','message']

        try {
            $email = (new TemplatedEmail())
                ->from(new Address(self::EMAIL_FROM, 'Formulaire Contact'))
                ->to(self::EMAIL_TO)
                ->replyTo(new Address($data['email'], $data['fullName'] ?: $data['email']))
                ->subject('Nouveau message de contact')
                ->htmlTemplate('pages/contact/emails/contact.html.twig')
                ->context([
                    'fullName'  => $data['fullName'],
                    'userEmail' => $data['email'], 
                    'bodyText'  => $data['message'],
                ]);

            $mailer->send($email);

            return new JsonResponse(['ok' => true, 'message' => 'Merci, votre message a été envoyé.']);
        } catch (\Throwable $e) {
            // en DEV, expose l’erreur pour diagnostiquer rapidement
            if ($this->getParameter('kernel.environment') === 'dev') {
                return new JsonResponse(['ok' => false, 'error' => $e->getMessage()], 500);
            }
            return new JsonResponse(['ok' => false, 'error' => 'Envoi impossible pour le moment.'], 500);
        }
    }

    /** Collecte propre des erreurs champ par champ pour le JSON (HTTP 422) */
    private function collectErrors(FormInterface $form): array
    {
        $errors = [];
        foreach ($form->all() as $child) {
            $msgs = [];
            foreach ($child->getErrors(true) as $e) {
                $msgs[] = $e->getMessage();
            }
            if ($msgs) {
                $errors[$child->getName()] = $msgs;
            }
        }
        return $errors;
    }
}
