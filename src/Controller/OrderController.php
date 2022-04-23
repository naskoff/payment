<?php

namespace App\Controller;

use DateTime;
use Stripe\Stripe;
use App\Entity\User;
use App\Entity\Order;
use App\Entity\Website;
use Stripe\PaymentIntent;
use PayPalHttp\IOException;
use PayPalHttp\HttpResponse;
use PayPalHttp\HttpException;
use Stripe\Exception\ApiErrorException;
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
    public function orderPayPal(Request $request): JsonResponse
    {

        try {
            $response = $this->captureOrder($request->request->get('orderId'));
        } catch (HttpException|IOException $e) {
            return new JsonResponse(['errors' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

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

    /**
     * @Route("/payment-intent/confirm", name="payment_intent_confirm")
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentIntentConfirm(Request $request): JsonResponse
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        try {
            $order = PaymentIntent::retrieve($request->request->get('paymentIntentId', null));
        } catch (ApiErrorException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        $orderEntity = (new Order())
            ->setOrderId($order['id'])
            ->setAmount($order['amount']/100)
            ->setProvider(Order::PROVIDER_STRIPE)
            ->setStatus(Order::STATUS_CONFIRM)
            ->setCreatedAt(new DateTime());

        $this->getDoctrine()->getManager()->persist($orderEntity);
        $this->getDoctrine()->getManager()->flush();

        return $this->json(['order' => $order]);
    }

    /**
     * @Route("/payment-intent", name="payment_intent")
     * @param Request $request
     * @return JsonResponse
     */
    public function paymentIntent(Request $request): JsonResponse
    {
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => $request->request->get('amount'),
                'currency' => 'usd',
            ]);
        } catch (ApiErrorException $e) {
            return $this->json(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }

        return $this->json(['clientSecret' => $paymentIntent->client_secret]);
    }
}
