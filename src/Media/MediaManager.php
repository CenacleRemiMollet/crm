<?php
namespace App\Media;

use Hoa\Iterator\FileSystem;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Exception\UnauthorizedTypeUploadException;
use App\Exception\FileTooLargeUploadException;

class MediaManager
{
	const MEDIA_FOLDER = "media";
	const MEDIA_FOLDER_CLUB_LOGO = 'club_logo';
	
	const MAX_LOGO_SIZE = 256 * 1024;

	private $projectPath;
	private $logger;

	public function __construct(KernelInterface $appKernel, LoggerInterface $logger)
	{
		$this->logger = $logger;
		$this->projectPath = $appKernel->getProjectDir();
	}

	public function find($category, $basename): Media
	{
		$folder = $this->getCategoryFolder($category);

		$finder = new Finder();
		foreach ($finder->in($folder)->name($basename.'*') as $file) {
			// $file is Symfony\Component\Finder\SplFileInfo
			return new Media($file, $this->projectPath);
		}
		return new Media(null, $this->projectPath);
	}

	public function upload($category, $uuid, UploadedFile $inFile)
	{
	    if($inFile->getSize() > MediaManager::MAX_LOGO_SIZE) {
	        throw new FileTooLargeUploadException($inFile->getSize(), MediaManager::MAX_LOGO_SIZE);
	    }
	    
	    $finfo = new \Finfo(FILEINFO_MIME);
	    $type = $finfo->file($inFile, FILEINFO_MIME_TYPE);
	    if($type !== 'image/gif'
	        && $type !== 'image/jpeg'
	        && $type !== 'image/png') {
	           throw new UnauthorizedTypeUploadException($type);
	    }
	    
// 	    $exif = exif_read_data($inFile, 'COMPUTED', true);
// 	    if($exif !== null) {
//     	    $exifComputed = $exif['COMPUTED'];
//     	    if($exifComputed !== null) {
//     	        // TODO do something with the image dimension ?
//     	        $this->logger->debug($exifComputed['Width'].'x'.$exifComputed['Height']);
//     	    }
// 	    }
	    
	    $this->logger->debug('upload: '.$inFile->getClientOriginalName());
		$folder = $this->getCategoryFolder($category);
		$newfname = $uuid.'.'.strtolower(pathinfo($inFile->getClientOriginalName(), PATHINFO_EXTENSION));
		$this->logger->debug('$newfname: '.$newfname);

		try {
			$inFile->move($folder, $newfname);
		} catch (FileException $e){
			$this->logger->error('failed to upload image: '.$e->getMessage());
			throw new FileException('Failed to upload file');
		}
		return $newfname;
	}

	public function save($inFile, $category, $basename)
	{
		$folder = $this->getCategoryFolder($category);
		$newfname = $folder.DIRECTORY_SEPARATOR.$basename.strtolower(substr($inFile, strrpos($inFile, '.')));
		try {
			$file = fopen($inFile, 'rb', false);
			if ($file) {
				$newf = fopen($newfname, 'wb');
				if ($newf) {
					while(!feof($file)) {
						fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
					}
				}
			}
			if ($file) {
				fclose($file);
			}
			if ($newf) {
				fclose($newf);
			}
		} catch(\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function downloadAndSave($url, $category, $basename)
	{
		$folder = $this->getCategoryFolder($category);
		$newfname = $folder.DIRECTORY_SEPARATOR.$basename.strtolower(substr($url, strrpos($url, '.')));
		try {
			$context = stream_context_create(array(
				'ssl' => array(
					'verify_peer' => false,
					'verify_peer_name' => false
				),
				'http' => array(
					'header' => 'User-Agent: Mozilla/5.0 (Linux) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.83 Safari/537.36')));
			$file = fopen($url, 'rb', false, $context);
			if ($file) {
				$newf = fopen ($newfname, 'wb');
				if ($newf) {
					while(!feof($file)) {
						fwrite($newf, fread($file, 1024 * 8), 1024 * 8);
					}
				}
			}
			if ($file) {
				fclose($file);
			}
			if ($newf) {
				fclose($newf);
			}
		} catch(\Exception $e) {
			echo $e->getMessage();
		}
	}

	public function delete($category, $fileName)
	{
		$folder = $this->getCategoryFolder($category);
		return unlink($folder.DIRECTORY_SEPARATOR.$fileName);
	}

	//***************************************************

	private function getCategoryFolder($category): string
	{
		$folder = $this->projectPath.DIRECTORY_SEPARATOR.self::MEDIA_FOLDER.DIRECTORY_SEPARATOR.$category;
		$this->logger->debug('Media category folder: '.$folder);
		if(! file_exists($folder)) {
			if(! mkdir($folder, 0777, true)) {
				throw new \Exception('Unable to make a folder: '.$folder);
			}
		}
		return $folder;
	}

}

