<?php

declare(strict_types=1);

namespace App\Controller;

use App\Service\CollectionService;
use App\Service\OrderService;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\Routing\Annotation\Route;

class OrderController extends AbstractController
{
    private string $auth;

    #[Route('/', name: 'app_order_render')]
    public function index(): Response
    {
        return $this->render('form_login.html.twig',[]);
    }

    #[Route('/order', name: 'app_order', methods: 'GET')]
    public function getOrder(PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page',1);
        $field = $request->query->get('sort');
        $direction = $request->query->getAlpha('direction');
        $auth = $request->headers->get('Authorization');
        $session = new Session(new NativeSessionStorage(), new AttributeBag());
        if (!empty($auth)) {
            $auth = str_replace("Bearer ","", $auth);
            $session->set('token', $auth);
        }
        $auth = $session->get('token');
        $orderService = new OrderService();
        $orders = $orderService->getOrder($auth,$direction,$field);
        $collectionService = new CollectionService();
        $collection = $collectionService->jsonToCollection($orders);
        $orders= $paginator->paginate($collection, $page,15);

        return $this->render('order.html.twig',[
            'orders' => $orders
        ]);
    }
}
