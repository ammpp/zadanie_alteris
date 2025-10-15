<?php
namespace App\Controller;

use App\Service\JednostkaService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;

class JednostkaListController extends AbstractController
{
    private JednostkaService $jednostkaService;
    private SerializerInterface $serializer;

    public function __construct(JednostkaService $jednostkaService, SerializerInterface $serializer)
	{
	    $this->jednostkaService = $jednostkaService;
	    $this->serializer = $serializer;
	}

	public function __invoke(): JsonResponse
	{
	    return new JsonResponse(
	        $this->serializer->serialize($this->jednostkaService->getJednostki(), 'json')
	    );
	}
}
