<?php

namespace Archibald\Request;

use GuzzleHttp\Client;

class Giphy extends \Archibald\Request
{
	protected $requestGifs = 'http://api.giphy.com/v1/gifs/random';
	protected $apiKey = 'dc6zaTOxFJmzC';

	public function __construct($request)
	{
		if ($request['body'] == 'tags') {
			echo 'Tag listing is unsupported in the current setup';
			return;
		}

		parent::__construct($request);
	}

	public function searchGif($requestString)
	{
		try {
			$response = $this->client->get(
				$this->requestGifs, [
					'query' => [
						'api_key' => $this->apiKey,
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
		
		$gif = json_decode($responseBody);
		$message = $gif->data->image_original_url;

		if (false !== $message) {
		  $this->postResponse($message);
		}
		else {
			echo 'No GIFs found with tag *' . $this->body . '*';
		}
	}
}
