<?php
namespace App\Media;

use Symfony\Component\Finder\SplFileInfo;

class Media
{
	private $file; // SplFileInfo
	private $projectPath;

	public function __construct(SplFileInfo $file = null, $projectPath = null)
	{
		$this->file = $file;
		$this->projectPath = $projectPath;
	}

	public function isFound(): bool
	{
		return $this->file != null;
	}

	public function getFile(): ?\SplFileInfo
	{
		return $this->file;
	}
	
	public function getFileOrDefault(string $relativePath): ?\SplFileInfo
	{
		return $this->isFound() ? $this->file : new \SplFileInfo($this->projectPath.DIRECTORY_SEPARATOR.$relativePath);
	}
}

