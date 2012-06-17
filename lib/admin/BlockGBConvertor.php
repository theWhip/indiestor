<?php
/*
        Indiestor program

	Written by Erik Poupaert, erik@sankuru.biz
        Commissioned at peopleperhour.com 
        By Alex Gardiner, alex.gardiner@canterbury.ac.uk
*/

class BlockGBConvertor
{
	static function deviceBlockSize($device)
	{
		$blockSize=sysquery_dumpe2fs_blocksize($device);
		if($blockSize==null)
		{
			ActionEngine::error("Cannot find block size for device '$device'",ERRNUM_CANNOT_FIND_BLOCKSIZE_FOR_DEVICE);
		}
		return $blockSize;
	}

	static function deviceGBToBlocks($device,$GB)
	{
		$blockSize=self::deviceBlockSize($device);
		$numBytesInGB=1024*1024*1024;
		$blocksInGB=$GB*$numBytesInGB/$blockSize;
		return $blocksInGB;
	}

	static function deviceBlocksToGB($device,$blocks)
	{
		$blockSize=self::deviceBlockSize($device);
		$numBytesInGB=1024*1024*1024;
		$totalNumBytes=$blocks*$blockSize;
		$GB=$totalNumBytes/$numBytesInGB;
		return $GB;
	}
}

