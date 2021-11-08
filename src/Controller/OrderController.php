<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Website;
use PayPalHttp\IOException;
use PayPalHttp\HttpResponse;
use PayPalHttp\HttpException;
use PayPalCheckoutSdk\Core\PayPalHttpClient;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use PayPalCheckoutSdk\Core\SandboxEnvironment;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use PayPalCheckoutSdk\Orders\OrdersCaptureRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class OrderController extends AbstractController
{
    /**
     * @Route("/order/paypal", name="order", methods={"POST"})
     */
    public function index(Request $request): JsonResponse
    {

        try {
            $response = $this->captureOrder($request->request->get('orderId'));
        } catch (HttpException | IOException $e) {
            return new JsonResponse(['errors' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        file_put_contents("C:/Users/zlatin.hristov/Desktop/response.txt",
            print_r($response, true) . PHP_EOL, FILE_APPEND);

        if (empty($response)) {
            return new JsonResponse(['errors' => 'order not found'], Response::HTTP_BAD_REQUEST);
        }

        /* @var $user User */
        $user = $this->getUser();

        $order = new Order();
        $order->setUser($user);
        $order->setCreatedAt(new DateTime());
        $order->setOrderId($request->request->get('orderId', null));

        $this->getDoctrine()->getManager()->persist($order);

        if (empty($user)) {
            $website = $this->getDoctrine()->getRepository(Website::class)->findOneBy([], []);
            if (empty($website)) {
                $website = new Website();
            }
            $website->setMoney($website->getMoney() + $response->result->capture);
        }

        $this->getDoctrine()->getManager()->flush();

        return new JsonResponse([], Response::HTTP_OK);
    }

    /**
     * @param string $orderId
     * @return HttpResponse
     * @throws HttpException
     * @throws IOException
     */
    public function captureOrder(string $orderId): HttpResponse
    {

        $clientId = $this->getParameter('paypal_client_id');
        $clientSecret = $this->getParameter('paypal_client_secret');

        $environment = new SandboxEnvironment($clientId, $clientSecret);
        $paypalHttpClient = new PayPalHttpClient($environment);

        $request = new OrdersCaptureRequest($orderId);

        return $paypalHttpClient->execute($request);
    }
}
