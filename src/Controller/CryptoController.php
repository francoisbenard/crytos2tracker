<?php

namespace App\Controller;

use App\Entity\Cryptolist;
use App\Entity\Mycrypto;
use App\Entity\Save;
use App\Form\BuyCryptoType;
use App\Form\RemoveCryptoQuantityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class CryptoController extends AbstractController
{

//   page liste cryptos
    #[Route('/home', name: 'home')]
    public function index(ManagerRegistry $doctrine): Response
    {
        // Récupération des data de l'API
        $dataAPI = $this->getAPI();
        // Récupération du calcul de la rentabilité
        $totalRentability = $this->getRentability($doctrine);
        // Récupération des mycryptos, ce sont ceux qu'on a acheté
        $myCryptos = $doctrine->getRepository(Mycrypto::class)->findAll();
        // Récupération de la liste des cryptos disponibles et que l'on a saisie en base de données
        $Cryptolist = $doctrine->getRepository(Cryptolist::class)->findAll();

        return $this->render('crypto/index.html.twig', [
            'myCryptos' => $myCryptos, 'dataAPI' => $dataAPI, 'rentability' => $totalRentability]);
    }

    // On déclenche la sauvegarde en allant sur /save (A mettre en place dans un CRON avec déclenchement quotidien)
    #[Route('/save', name: 'save')]
    public function save(ManagerRegistry $doctrine): response
    {
        $dailySaved = $doctrine->getRepository(Save::class);
        $totalRentability = $this->getRentability($doctrine);
        $today = date('Y-m-d');
        // On vérifie qu'il n'y a pas eu déjà une sauvegarde aujourd'hui
        if ($dailySaved->findByDate($today) == null) {
            $saveRentability = new Save();
            $saveRentability->setDate($today);
            $saveRentability->setTotal(round($totalRentability));
            $entityManager = $doctrine->getManager();
            $entityManager->persist($saveRentability);
            $entityManager->flush();
        }
        return $this->redirectToRoute('home');
    }


    // Fonction pour récupérer les data de l'API (code issu de la documentation)

    public function getAPI()
    {
        $url = 'https://pro-api.coinmarketcap.com/v1/cryptocurrency/quotes/latest';
        $parameters = [
            // on récupère les 10 cryptos les plus courantes.
            'symbol' => 'BTC,ETH,BNB,USDT,SOL,XRP,ADA,USDC,LUNA,AVAX',
            'convert' => 'EUR'
        ];
        $headers = [
            'Accepts: application/json',
            'X-CMC_PRO_API_KEY: a1c49f65-f552-436c-81af-9c9170a7fac9'
        ];
        $qs = http_build_query($parameters); // query string encode the parameters
        $request = "{$url}?{$qs}"; // create the request URL
        $curl = curl_init(); // Get cURL resource
        // Set cURL options
        curl_setopt_array($curl, array(
            CURLOPT_URL => $request,            // set the request URL
            CURLOPT_HTTPHEADER => $headers,     // set the headers
            CURLOPT_RETURNTRANSFER => 1         // ask for raw response instead of bool
        ));
        $response = curl_exec($curl); // Send the request, save the response
        curl_close($curl); // Close request
        $dataAPI = json_decode($response, true);// print json decoded response
        return $dataAPI['data'];
    }

    // Calcul de la rentabilité pour une crypto = (prix acheté * quantité acheté) - (prix API * quantité acheté)
    // Donc on boucle avec pour prendre en compte la quantité

    public function getRentability(ManagerRegistry $doctrine): float
    {
        $dataAPI = $this->getAPI();
        $myCryptos = $doctrine->getRepository(Mycrypto::class)->findAll();
        $totalWithMyCryptoPrice = 0;
        $TotalWithApiPrice = 0;
        foreach ($myCryptos as $crypto) {
            $totalWithMyCryptoPrice += $crypto->getPrice() * $crypto->getQuantity();
            foreach ($dataAPI as $key => $value) {
                if ($value["symbol"] === $crypto->getCrypto()->getSymbol()) {

                    $TotalWithApiPrice += $value["quote"]["EUR"]["price"] * $crypto->getQuantity();
                }
            }
        }
        $rentability = round($totalWithMyCryptoPrice - $TotalWithApiPrice);
        return $rentability;
    }

    // achat de cryptos
    #[Route('/buycrypto', name: 'app_buycrypto')]
    public function newCrypto(Request $request, ManagerRegistry $doctrine): Response
    {
        $crypto = new Mycrypto();
        $form = $this->createForm(BuyCryptoType::class, $crypto);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $doctrine->getManager();
            $entityManager->persist($crypto);
            $entityManager->flush();
            // si l'achat est validé, on retourne sur la home
            return $this->redirectToRoute('home');
        }
        // si l'achat n'est pas validé, on reste sur le formulaire
        return $this->renderForm('crypto/buyCrypto.html.twig', ['form' => $form]);
    }

    // Suppression de crypto
    #[Route('/removecrypto', name: 'app_removecrypto')]
    public function removeCrypto(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(RemoveCryptoQuantityType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();
            // on vérifie que l'on ne dépasse pas la quantité de crypto que l'on peut supprimer
            $removeQuantity = $data["quantity"];
            $cryptoQuantity = $data["crypto"]->getQuantity();
            $cryptoListId = $data["crypto"]->getCrypto()->getId();
            $cryptoName = $doctrine->getRepository(Cryptolist::class)->find($cryptoListId)->getName();
            $message = 'Vous disposez actuellement de ' . $cryptoQuantity . ' ' . $cryptoName . '(s).';
            $message2 = 'Saisissez une quantité inférieure pour supprimer vos cryptos.';
            // si la quantité à supprimer est supérieur à la quantité que l'on possède, on affiche un message. Le formulaire n'est pas validé
            if ($removeQuantity > $cryptoQuantity) {
                return $this->renderForm('crypto/removeQuantityCrypto.html.twig', ['form' => $form, 'message' => $message, 'message2' => $message2]);
            } else {
                // début de la suppression
                $newQuantity = $cryptoQuantity - $removeQuantity;
                $myCryptoId = $data["crypto"]->getId();
                $myCryptos = $doctrine->getRepository(Mycrypto::class)->find($myCryptoId);
                $entityManager = $doctrine->getManager();
                // si la quantité est égale à zéro, on ne met pas à jour la quantité mais on supprime directement cette crypto
                if ($newQuantity == 0) {
                    $entityManager->remove($myCryptos);
                    $entityManager->flush();
                    return $this->redirectToRoute('home');
                }
                $myCryptos->SetQuantity($newQuantity);
                $entityManager->persist($myCryptos);
                $entityManager->flush();
                return $this->redirectToRoute('home');
            }
        }
        return $this->renderForm('crypto/removeQuantityCrypto.html.twig', ['form' => $form, 'message' => '', 'message2' => '']);
    }


    // Graphique de la rentabilité, on récupère les sauvegardes pour l'alimenter
    /**
     * @Route("/chart", name="chart")
     */
    public function chart(ChartBuilderInterface $chartBuilder, ManagerRegistry $doctrine): Response
    {
        $entityManager = $doctrine->getManager();
        $dailyBackup = $entityManager->getRepository(Save::class);
        $listDailyBackup = $dailyBackup->findAll();
        $listDailyBackupDays = array();
        $listDailyBackupValue = array();
        foreach ($listDailyBackup as $DailyBackup) {
            array_push($listDailyBackupDays, $DailyBackup->getDate());
            array_push($listDailyBackupValue, $DailyBackup->getTotal());
        }
        $chart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $chart->setData([
            'labels' => $listDailyBackupDays,
            'datasets' => [
                [
                    'radius' => 3,
                    'borderColor' => 'rgb(31,195,108)',
                    'tension' => 0.2,
                    'data' => $listDailyBackupValue,
                    'borderWidth' => 5,
                ],
            ],
        ]);
        $chart->setOptions([
            'plugins' => [
                'legend' => [
                    'display' => false,
                ]
            ]
        ]);
        return $this->render('crypto/chart.html.twig', [
            'chart' => $chart,
        ]);
    }
}

