<?php

namespace App\Controller\Api;

use App\Repository\WalletRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetWalletController extends AbstractController
{
    #[Route('/api/get/wallet', name: 'app_api_get_wallet')]
    public function index(WalletRepository $wallets): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $wallet = $wallets->findOneBy(['owner' => $user]);

        if (!$wallet) {
            return $this->json(['error' => 'Wallet not found'], Response::HTTP_NOT_FOUND);
        }

        $transactions = $wallet->getWalletTransactions();

        return $this->json([
            'id' => $wallet->getId(),
            'balance' => $wallet->getBalance(),
            'transactions' => $transactions->map(function($transaction) {
                return [
                    'id' => $transaction->getId(),
                    'type' => $transaction->getType(),
                    'amount' => $transaction->getAmount(),
                ];
            })->toArray(),
        ]);

       
    }
}
