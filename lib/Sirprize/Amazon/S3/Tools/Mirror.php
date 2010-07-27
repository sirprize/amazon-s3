<?php

/**
 * Amazon S3 API Wrapper for PHP 5.3+ 
 *
 * LICENSE
 *
 * This source file is subjekt to the MIT license that is bundled
 * with this package in the file LICENSE.txt
 *
 * @category   Sirprize
 * @package    Amazon\S3
 * @copyright  Copyright (c) 2010, Christian Hoegl, Switzerland (http://sirprize.me)
 * @license    MIT License
 */


namespace Sirprize\Amazon\S3\Tools;


use \Sirprize\Amazon\S3;


class Mirror
{
	
	protected $_s3 = null;
	protected $_force = false; # overwrite destination if exists
	protected $_numRetries = 2;
	#protected $_printMessages = false;
	
	// content-handling
	protected $_excludePatterns = array();
	protected $_targetRenamePatternFind = null;
	protected $_targetRenamePatternReplace = null;
	
	// summary
	protected $_countKeys = 0;
	protected $_countExcluded = 0;
	protected $_countSkipped = 0;
	protected $_countReplaced = 0;
	protected $_countCopied = 0;
	protected $_countErrors = 0;
	
	// event-listener
	protected $_eventManager = null;
	
	
	
	
	
	public function setS3(S3 $s3)
	{
		$this->_s3 = $s3;
		return $this;
	}
	
	
	public function getS3()
	{
		if($this->_s3 === null)
		{
			throw new S3\Exception('call setS3() before '.__METHOD__);
		}
		
		return $this->_s3;
	}
	
	
	public function setEventManager(\Doctrine\Common\EventManager $eventManager)
	{
		$this->_eventManager = $eventManager;
		return $this;
	}
	
	
	public function getEventManager()
	{
		if($this->_eventManager === null)
		{
			throw new S3\Exception('call setEventManager() before '.__METHOD__);
		}
		
		return $this->_eventManager;
	}
	
	
	public function getEvents()
	{
		return $this->_events;
	}
	
	
	public function addEventsToListener(S3\Core\EventListener $listener)
	{
		foreach($this->_events as $event)
		{
			$this->getEventManager()->addEventListener($event, $listener);
		}
		
		return $this;
	}
	
	
	public function force($onOff)
	{
		$this->_force = $onOff;
		return $this;
	}
	
	
	public function setNumRetries($numRetries)
	{
		$this->_numRetries = $numRetries;
		return $this;
	}
	
	/*
	public function printMessages($onOff)
	{
		$this->_printMessages = $onOff;
		return $this;
	}
	* /
	
	public function printSummary()
	{
		print "=====================================\n";
		print "EXCLUDED: {$this->_countExcluded}\n";
		print "SKIPPED: {$this->_countSkipped}\n";
		print "REPLACED: {$this->_countReplaced}\n";
		print "COPIED: {$this->_countCopied}\n";
		print "ERRORS: {$this->_countErrors}\n";
		print "TOTAL: {$this->_countKeys}\n";
		print "=====================================\n";
		return $this;
	}
	*/
	
	public function addExcludePattern($pattern)
	{
		$this->_excludePatterns[] = $pattern;
		return $this;
	}
	
	
	public function setExcludePatterns(array $patterns)
	{
		$this->_excludePatterns = $patterns;
		return $this;
	}
	
	
	public function setTargetRenamePattern($find, $replace)
	{
		$this->_targetRenamePatternFind = $find;
		$this->_targetRenamePatternReplace = $replace;
		return $this;
	}
	
	
	const EVENT_START_RUN = 'startRun';
	const EVENT_BUCKET_GET_ERROR = 'bucketGetError';
	const EVENT_OBJECT_EXCLUDED = 'objectExcluded';
	const EVENT_OBJECT_SKIPPED = 'objectSkipped';
	const EVENT_OBJECT_ERROR = 'objectError';
	const EVENT_OBJECT_REPLACED = 'objectReplaced';
	const EVENT_OBJECT_COPIED = 'objectCopied';
	const EVENT_SUMMARY = 'summary';
	
	
	
	protected $_events = array(
		self::EVENT_START_RUN,
		self::EVENT_BUCKET_GET_ERROR,
		self::EVENT_OBJECT_EXCLUDED,
		self::EVENT_OBJECT_SKIPPED,
		self::EVENT_OBJECT_ERROR,
		self::EVENT_OBJECT_REPLACED,
		self::EVENT_OBJECT_COPIED,
		self::EVENT_SUMMARY
	);
	
	
	protected $_maxExceptions = 100;
	protected $_numExceptions = 0;
	
	
	public function run(S3\Bucket $sourceBucket, S3\Bucket $targetBucket, $sourcePrefix = '', $sourceDelimiter = '', S3\Headers $headers = null)
	{
		$eventArgs =$this->getS3()->getEventArgsInstance();
		$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::INFO)->setMessage(__METHOD__);
		$this->getEventManager()->dispatchEvent(self::EVENT_START_RUN, $eventArgs);
		
		$this->_countKeys = 0;
		$this->_countExcluded = 0;
		$this->_countSkipped = 0;
		$this->_countReplaced = 0;
		$this->_countCopied = 0;
		$this->_countErrors = 0;
		
		$objekts = null;
		$lastKey = null; // last object is used as marker for next request

		while($objekts === null || $objekts->getIsTruncated())
		{
			try {
				$objekts = $this->getS3()->getObjektsInstance();
				$objekts
					->setPrefix($sourcePrefix)
					->setMarker($lastKey)
					->startAllInBucket($sourceBucket, $this->_numRetries)
				;

				foreach($objekts as $objekt)
				{
					$this->_copy($objekt, $targetBucket, clone $headers);
					$lastKey = $objekt->getKey();
				}
			}
			catch (\Exception $exception)
			{
				$this->_numExceptions++;
				
				if($this->_numExceptions == $this->_maxExceptions)
				{
					exit;
				}
				
				$eventArgs =$this->getS3()->getEventArgsInstance();
				$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::ERR)->setMessage(__METHOD__.' // '.$exception->getMessage());
				$this->getEventManager()->dispatchEvent(self::EVENT_BUCKET_GET_ERROR, $eventArgs);
			}
		}
		
		$summary  = "=====================================\n";
		$summary .= "EXCLUDED: {$this->_countExcluded}\n";
		$summary .= "SKIPPED: {$this->_countSkipped}\n";
		$summary .= "REPLACED: {$this->_countReplaced}\n";
		$summary .= "COPIED: {$this->_countCopied}\n";
		$summary .= "ERRORS: {$this->_countErrors}\n";
		$summary .= "TOTAL: {$this->_countKeys}\n";
		$summary .= "=====================================\n";
		
		$eventArgs =$this->getS3()->getEventArgsInstance();
		$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::INFO)->setMessage($summary);
		$this->getEventManager()->dispatchEvent(self::EVENT_SUMMARY, $eventArgs);
		
		return array(
			'excluded' => $this->_countExcluded,
			'skipped' => $this->_countSkipped,
			'replaced' => $this->_countReplaced,
			'copied' => $this->_countCopied,
			'errors' => $this->_countErrors,
			'keys' => $this->_countKeys
		);
	}
	
	
	
	
	
	protected function _copy(S3\Objekt $objekt, S3\Bucket $targetBucket, S3\Headers $headers = null)
	{
		if($headers === null)
		{
			$headers = $this->getS3()->getHeadersInstance();
		}
		
		$this->_countKeys++;
		$targetKey = $objekt->getKey();
		
		if($this->_targetRenamePatternFind !== null && $this->_targetRenamePatternReplace !== null)
		{
			$targetKey = preg_replace(
				$this->_targetRenamePatternFind,
				$this->_targetRenamePatternReplace,
				$objekt->getKey()
			);
		}
		
		$task = __METHOD__.' // '.$objekt->getBucket()->getName().'/'.$objekt->getKey().' > '.$targetBucket->getName().'/'.$targetKey;
		
		foreach($this->_excludePatterns as $pattern)
		{
			if(preg_match($pattern, $objekt->getKey()))
			{
				$this->_countExcluded++;
				#$this->_print("$task // EXCLUDED\n");
				$eventArgs =$this->getS3()->getEventArgsInstance();
				$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::INFO)->setMessage("$task // EXCLUDED");
				$this->getEventManager()->dispatchEvent(self::EVENT_OBJECT_EXCLUDED, $eventArgs);
				return;
			}
		}
		
		$newObjekt = $this->getS3()->getObjektsInstance()->getObjektInstance();
		$newObjekt
			->setBucket($targetBucket)
			->setKey($targetKey)
		;
		
		$targetExists = false;
		
		try {
			if(!$this->_force)
			{
				# check if target exists
				$newObjekt->head($this->_numRetries);
				
				if($newObjekt->getResponseHandler()->getHttpResponse()->getStatus() == 200)
				{
					$targetExists = true;
					
					if($newObjekt->getETag() !== null)
					{
						# set precondition
						$headers->add("x-amz-copy-source-if-none-match: ".$newObjekt->getETag());
					}
				}
				
				#print $newObjekt->getRestClient()->getHttpClient()->getLastResponse()."\n\n\n";
			}
			
			$objekt->copy($newObjekt, $headers, $this->_numRetries, array(412));
			
			if($objekt->getResponseHandler()->getHttpResponse()->getStatus() == 412) // precondition failed
			{
				$this->_countSkipped++;
				#$this->_print("$task // SKIPPED (EXISTS)\n");
				$eventArgs =$this->getS3()->getEventArgsInstance();
				$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::INFO)->setMessage("$task // SKIPPED (EXISTS)");
				$this->getEventManager()->dispatchEvent(self::EVENT_OBJECT_SKIPPED, $eventArgs);
				return;
			}
			
			if($objekt->getResponseHandler()->getHttpResponse()->getStatus() == 200)
			{
				if($objekt->getResponseHandler()->isError())
				{
					// error occurred during copy operation
					$this->_countErrors++;
					#$this->_print("$task // ERROR // ".$objekt->getResponseHandler()->getMessage()." (".$objekt->getResponseHandler()->getCode().")\n");
					$eventArgs =$this->getS3()->getEventArgsInstance();
					$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::ERR)->setMessage("$task // ERROR // ".$objekt->getResponseHandler()->getMessage()." (".$objekt->getResponseHandler()->getCode().")");
					$this->getEventManager()->dispatchEvent(self::EVENT_OBJECT_ERROR, $eventArgs);
					return;
				}
				else if($targetExists)
				{
					$this->_countReplaced++;
					#$this->_print("$task // REPLACE OK\n");
					$eventArgs =$this->getS3()->getEventArgsInstance();
					$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::INFO)->setMessage("$task // REPLACE OK");
					$this->getEventManager()->dispatchEvent(self::EVENT_OBJECT_REPLACED, $eventArgs);
					return;
				}
				else {
					$this->_countCopied++;
					#$this->_print("$task // COPY OK\n");
					$eventArgs =$this->getS3()->getEventArgsInstance();
					$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::INFO)->setMessage("$task // COPY OK");
					$this->getEventManager()->dispatchEvent(self::EVENT_OBJECT_COPIED, $eventArgs);
					return;
				}
			}
		}
		catch (\Exception $exception)
		{
			$this->_countErrors++;
			#$msg = "$task // ERROR (".$exception->getMessage().")\n";
			$eventArgs =$this->getS3()->getEventArgsInstance();
			$eventArgs->setSourceObject($this)->setType(S3\Core\EventArgs::ERR)->setMessage("$task // ERROR (".$exception->getMessage().")");
			$this->getEventManager()->dispatchEvent(self::EVENT_OBJECT_ERROR, $eventArgs);
			#$this->_print($msg);
		}
	}
	
	
	/*
	protected function _print($msg)
	{
		if($this->_printMessages)
		{
			print $msg;
		}
	}
	*/
}