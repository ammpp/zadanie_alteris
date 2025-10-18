<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends AbstractController
{
	public function __invoke(): Response
	{
		return new Response('
			<b>GET</b> /api/material - Get all materials<br />
			<hr />
			<b>GET</b> /api/material/{id} - Get specified material<ul>
			</ul>
			<hr />
			<b>POST</b> /api/material - Create material<ul>
			<li>kod (string) - code</li>
			<li>nazwa (string) - name</li>
			<li>grupa (int) - group id</li>
			<li>jednostka (int) - unit id</li>
			<li>wartosc (float) - value</li>
			</ul>
			<hr />
			<b>PUT</b> /api/material/{id} - Edit material<ul>
			<li>kod (string) - code</li>
			<li>nazwa (string) - name</li>
			<li>grupa (int) - group id</li>
			<li>jednostka (int) - unit id</li>
			<li>wartosc (float) - value</li>
			</ul>
			<hr />
			<b>DELETE</b> /api/material/{id} - Delete material<ul>
			</ul>
			<hr />
			<b>GET</b> /api/grupa - List of groups<ul>
			</ul>
			<hr />
			<b>GET</b> /api/grupa/{id} - List of groups starting from ID<ul>
			</ul>
			<hr />
			<b>POST</b> /api/grupa - Create group<ul>
			<li>nazwa (string) - name</li>
			<li>parent (int) - parent group id (0 for main group)</li>
			</ul>
			<hr />
			<b>PUT</b> /api/grupa/{id} - Edit group<ul>
			<li>nazwa (string) - name</li>
			<li>parent (int) - parent group id (0 for main group)</li>
			</ul>
			<hr />
			<b>DELETE</b> /api/grupa/{id} - Delete group<br />
			<hr />
			<b>GET</b> /api/jednostka - List of units<ul>
			</ul>
			<hr />
			<b>GET</b> /api/jednostka/{id} - Get specified unit<ul>
			</ul>
			<hr />
			<b>POST</b> /api/jednostka - Create unit<ul>
			<li>skrot (string) - short name</li>
			<li>nazwa (string) - name</li>
			</ul>
			<hr />
			<b>PUT</b> /api/jednostka/{id} - Edit unit<ul>
			<li>skrot (string) - short name</li>
			<li>nazwa (string) - name</li>
			</ul>
			<hr />
			<b>DELETE</b> /api/jednostka/{id} - Delete unit<br />
        ');
	}
}
