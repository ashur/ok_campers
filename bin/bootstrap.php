<?php

use Cranberry\Filesystem;
use Cranberry\Shell;
use Cranberry\Shell\Input;
use Cranberry\Shell\Output;
use Cranberry\Shell\Middleware;

use Abraham\TwitterOAuth\TwitterOAuth;

$___bootstrap = function( Shell\Application &$app )
{
	/*
	 * Commands
	 */
	$___tweet = function( Input\InputInterface $input, Output\OutputInterface $output )
	{
		$requiredEnvs = ['OKC_CONSUMER_KEY', 'OKC_CONSUMER_SECRET', 'OKC_ACCESS_TOKEN', 'OKC_ACCESS_TOKEN_SECRET'];

		foreach( $requiredEnvs as $requiredEnv )
		{
			if( !$input->hasEnv( $requiredEnv ) )
			{
				throw new \RuntimeException( "Missing required environment variable {$requiredEnv}" );
			}
		}

		$consumerKey = $input->getEnv( 'OKC_CONSUMER_KEY' );
		$consumerKeySecret = $input->getEnv( 'OKC_CONSUMER_SECRET' );
		$accessKey = $input->getEnv( 'OKC_ACCESS_TOKEN' );
		$accessKeyToken = $input->getEnv( 'OKC_ACCESS_TOKEN_SECRET' );

		$connection = new TwitterOAuth( $consumerKey, $consumerKeySecret, $accessKey, $accessKeyToken );

		$sourceFile = dirname( __DIR__ ) . '/source.gif';
		$media1 = $connection->upload( 'media/upload', ['media' => $sourceFile ] );
		$parameters = ['media_ids' => $media1->media_id_string];

		$result = $connection->post( "statuses/update", $parameters );
	};
	$app->pushMiddleware( new Middleware\Middleware( $___tweet ) );
	$app->setCommandDescription( 'tweet', 'Post to Twitter' );
	$app->setCommandUsage( 'tweet', 'tweet' );

	/*
	 * Error Middleware
	 */
	$___runtime = function( Input\InputInterface $input, Output\OutputInterface $output, \RuntimeException $exception )
	{
		$output->write( sprintf( '%s: %s', $this->getName(), $exception->getMessage() ) . PHP_EOL );
	};
	$app->pushErrorMiddleware( new Middleware\Middleware( $___runtime, \RuntimeException::class ) );
};
