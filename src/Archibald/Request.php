<?php

namespace Archibald;

use GuzzleHttp\Client;

class Request
{
	private $body;
	private $channel;
	private $user;

	private $requestGifs = 'http://replygif.net/api/gifs';
	private $requestTags = 'http://replygif.net/api/tags';
	private $apiKey = '39YAprx5Yi';

	private $client;

	public function __construct($request)
	{
		$this->body = $request['body'];
		$this->channel = $request['channel'];
		$this->user = $request['user'];

		$this->client = new Client();

		$this->parseRequestType();
	}

	public function parseRequestType()
	{
		switch ($this->body) {
			case 'shaq':
				$shaq = $this->staticRequest('shaq');
				break;

			case 'kannste':
			case 'kannsteschonsomachen':
			case 'kannstemachen':
			case 'kacke':
				$kannste = $this->staticRequest('kannste');
				break;

			case 'tags':
				$tags = $this->searchTags($this->body);
				break;

			case '';
				break;

			default:
				$search = $this->searchGif($this->body);
				break;
		}
	}

	private function staticRequest($request)
	{
		$responseBody = '';

		switch ($request) {
			case 'shaq':
				$responseBody = 'http://replygif.net/i/1106.gif';
				break;
			case 'kannste':
				$responseBody = 'http://i.imgur.com/D6iqV0b.png';
				break;
		}

		$this->postResponse($responseBody);
	}

	public function searchGif($requestString)
	{
		try {
			$response = $this->client->get(
				$this->requestGifs, [
					'query' => [
						'api-key' => $this->apiKey,
						'tag' => $requestString
					]
				]
			);
		}
		catch (RequestException $e) {
		    echo $e->getRequest();
		    if ($e->hasResponse()) {
		        $this->postResponse($e->getResponse());
		    }
		}
		$responseBody = $response->getBody();
		$message = $this->randomGif($responseBody);

		$this->postResponse($message);
	}

	public function searchTags($requestString)
	{
		try {
			$response = $this->client->get(
				$this->requestTags, [
					'query' => [
						'api-key' => $this->apiKey,
						'reaction' => 1
					]
				]
			);
		}
		catch (RequestException $e) {
			echo $e->getRequest();
		    if ($e->hasResponse()) {
		        $this->postResponse($e->getResponse());
		    }
		}

		$responseBody = $response->getBody();
		$message = $this->getTagList($responseBody);
	}

	public function randomGif($responseBody)
	{
		$gifs = json_decode($responseBody);

		$size = count($gifs);
		$randomIndex = rand(0, $size-1);

		return $gifs[$randomIndex]->file;
	}

	public function getTagList($responseBody)
	{
		$tags = json_decode($responseBody);

		$tagList = '';

		foreach ($tags as $tag) {
			$tagList .= $tag->title . " (" . $tag->count . ")\t";
		}

		echo $tagList;
	}

	public function postResponse($message)
	{
		$finalMessage = $this->user . " replies with «" . $this->body . "»\n" . $message;
		$request = $this->client->createRequest(
			'POST',
			SLACKBOT_HOOK . '&channel=' . $this->channel,
			[
				'body' => $finalMessage
			]
		);
        $this->client->send($request);
	}
}
