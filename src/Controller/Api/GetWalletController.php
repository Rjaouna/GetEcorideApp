<?php

namespace App\Controller\Api;

use App\Repository\WalletRepository;
use App\Repository\WalletTransactionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GetWalletController extends AbstractController
{
    #[Route('/api/get/wallet', name: 'app_api_get_wallet')]
    public function index(WalletRepository $wallets, WalletTransactionRepository $wallet_transaction): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['error' => 'User not authenticated'], Response::HTTP_UNAUTHORIZED);
        }

        $wallet = $wallets->findOneBy(['owner' => $user]);

        if (!$wallet) {
            return $this->json(['error' => 'Wallet not found'], Response::HTTP_NOT_FOUND);
        }

        $transactions = $wallet_transaction->findBy(['wallet' => $wallet]);

        $totalDebit = 0;

        foreach ($transactions as $transaction) {
            if ($transaction->getType() === 'debit') {
                // On additionne la valeur absolue pour le total
                $totalDebit += abs($transaction->getAmount());
            } else {
                $totalDebit -= abs($transaction->getAmount());
            }
        }


        return $this->json([
            'id' => $wallet->getId(),
            'balance' => $wallet->getBalance(),
            'transactions' => $totalDebit,
        ]);

       
    }
}
