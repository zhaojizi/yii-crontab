<?php 
/**
 * Copyright (c) 2010 David Soyez, http://code.google.com/p/yii-crontab/
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *  
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *  
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 * 
 * CCrontab helps to add system cron jobs
 *
 * @author David Soyez <david.soyez@yiiframework.fr>
 * @link http://code.google.com/p/yii-crontab/
 * @copyright Copyright &copy; 2009-2010 yiiframework.fr
 * @license http://www.opensource.org/licenses/mit-license.php
 * @version 1.0
 * @package crontab
 * @since 1.0
 */
class CCrontab extends CApplicationComponent{
	
	protected $minute		= NULL;
	protected $hour			= NULL;
	protected $day			= NULL;
	protected $month		= NULL;
	protected $dayofweek	= NULL;
	protected $command		= NULL;
	protected $directory	= NULL;
	protected $filename		= "crons";
	protected $crontabPath	= NULL;
	protected $handle		= NULL;
	
	/**
	 *	Constructor. Attempts to create directory for
	 *	holding cron jobs
	 *
	 *	@param	string	$dir		 Directory to hold cron job files (slash terminated)
	 *	@param	string	$filename	 Filename to write to
	 *	@param	string	$crontabPath Path to cron program
	 *	@access	public
	 */
	function CCrontab($dir=NULL, $filename=NULL, $crontabPath=NULL){
		$result				=(!$dir) ? $this->setDirectory(Yii::getPathOfAlias('application.extensions.crontab.crontabs').'/') : $this->setDirectory($dir);
		if(!$result)
			exit('Directory error');
		$result				=(!$filename) ? $this->createCronFile("crons") : $this->createCronFile($filename);
		if(!$result)
			exit('File error');
		$this->crontabPath=($crontabPath) ? NULL : $crontabPath;
	}
	
	
	/**
	 *	Set the directory path. Will check it if it exists then
	 *	try to open it. Also if it doesn't exist then it will try to
	 *	create it, makes it with mode 0700
	 *
	 *	@param	string	$directory	Directory, relative or full path
	 *	@access	public
	 *	@return	boolean
	 */
	protected function setDirectory($directory){
		if(!$directory) return false;
		
		if(is_dir($directory)){
			if($dh=opendir($directory)){
				$this->directory=$directory;
				return $this;
			}else
				return false;
		}else{
			if(mkdir($directory, 0700)){
				$this->directory=$directory;
				return $this;
			}
		}
		return false;
	}
	
	
	/**
	 *	Create cron file
	 *
	 *	This will create a cron job file for you and set the filename
	 *	of this class to use it. Make sure you have already set the directory
	 *	path variable with the consructor. If the file exists and we can write
	 *	it then return true esle false. Also sets $handle with the resource handle
	 *	to the file
	 *
	 *	@param	string	$filename	Name of file you want to create
	 *	@access	public
	 *	@return	boolean
	 */
	protected function createCronFile($filename=NULL){
		if(!$filename)
			return false;
		
		if(file_exists($this->directory.$filename)){
			if($this->openFile($handle,$filename, 'a')){
				$this->handle=&$handle;
				$this->filename=$filename;
				return $this;
			}else
				return false;
		}
		
		if(!$this->openFile($handle,$filename, 'a'))
			return false;
		else{
			$this->handle=&$handle;
			$this->filename=$filename;
			return $this;
		}
	}
		
	
	

	/**
	 *	Set date parameters
	 *
	 *	If any parameters are left NULL then they default to *
	 *
	 *	A hyphen (-) between integers specifies a range of integers. For
	 *	example, 1-4 means the integers 1, 2, 3, and 4.
	 *
	 *	A list of values separated by commas (,) specifies a list. For
	 *	example, 3, 4, 6, 8 indicates those four specific integers.
	 *
	 *	The forward slash (/) can be used to specify step values. The value
	 *	of an integer can be skipped within a range by following the range
	 *	with /<integer>. For example, 0-59/2 can be used to define every other
	 *	minute in the minute field. Step values can also be used with an asterisk.
	 *	For instance, the value * /3 (no space) can be used in the month field to run the
	 *	task every third month...
	 *
	 *	@param	mixed	$min		Minute(s)... 0 to 59
	 *	@param	mixed	$hour		Hour(s)... 0 to 23
	 *	@param	mixed	$day		Day(s)... 1 to 31
	 *	@param	mixed	$month		Month(s)... 1 to 12 or short name
	 *	@param	mixed	$dayofweek	Day(s) of week... 0 to 7 or short name. 0 and 7 = sunday
	 *	$access	public
	 */
	function setDateParams($min=NULL, $hour=NULL, $day=NULL, $month=NULL, $dayofweek=NULL){
		
		if($min=="0")
			$this->minute=0;
		elseif($min)
			$this->minute=$min;
		else
			$this->minute="*";
		
		if($hour=="0")
			$this->hour=0;
		elseif($hour)
			$this->hour=$hour;
		else
			$this->hour="*";
		$this->month=($month) ? $month : "*";
		$this->day=($day) ? $day : "*";
		$this->dayofweek=($dayofweek) ? $dayofweek : "*";
		
		
		return $this;
		
	}

	
	/**
	 *	Set command to execute
	 *
	 *	@param	string	$command	Comand to set
	 *	@access	public
	 *	@return	string	$command
	 */
	function setCommand($command){
		$this->command=$command;

		return $this;
	}
	
	
	/**
	 *	Set a application command to execute
	 *
	 *	@param	string	$command	Comand to set
	 *	@access	public
	 *	@return	string	$command
	 */
	function setApplicationCommand($entryScript, $commandName){
		
		$command = '';
		$nb_params = func_num_args() - 2;
		
		$command = 'php '.Yii::getPathOfAlias('webroot').'/'.$entryScript . '.php ' . $commandName;
				
		if ($nb_params >= 1)
		{
			for ($i=1;$i<=$nb_params;$i++)
			{
				$command .= ' ' . func_get_arg($i + 1);
			}
			
		}

		$this->command=$command;
		
		return $this;
	}
	
	
	
	/**
	 *	Write cron command to file. Make sure you used createCronFile
	 *	before using this function of it will return false
	 *
	 *	@access	public
	 *	@return	void
	 */
	function saveCronFile(){
		$command=$this->minute." ".$this->hour." ".$this->day." ".$this->month." ".$this->dayofweek." ".$this->command."\n";
		if(!fwrite($this->handle, $command))
			return false;
		else
			return $this;
	}
	
	
	
	function emptyCrontab()
	{
		$this->closeFile();
		$this->openFile($this->handle,$this->filename, 'w');
		$this->closeFile();
		$this->openFile($this->handle,$this->filename, 'a');
		
		return $this;
	}
	
	
	
	/**
	 *	Save cron in system
	 *
	 *	@access	public
	 *	@return boolean				true if successful else false
	 */
	function addToCrontab(){
		
		if(!$this->filename)
			exit('No name specified for cron file');
					
		if(exec($this->crontabPath."crontab ".$this->directory.$this->filename))
			return $this;
		else
			return false;
	}
	
	
	protected function openFile(& $handle,$filename, $accessType = 'a')
	{
		 return $handle = fopen($this->directory.$filename, $accessType);
		
	}	
	
	
	protected function closeFile()
	{
		fclose($this->handle);
	}
	
}
