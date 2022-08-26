<?php
ini_set('memory_limit', '-1');
class imageRGB{
	private $file;
	private  $fileName;
	private  $fileTmpName;
	private  $fileSize;
	private  $fileError;
	private  $fileType;
	private $fileExt;
	private $fileActualExt;
	private $allowed;
	private $location;
	private $width;
	private $height;
	private $pixelColorArray;

	private $arrayArray;
	private $counter;
	private $Rangecounter;
	private $fivePopularRgb;
	private $tempArray;
	private $sumOfType;
	private $resized;
	private $fivePercentage;
	private $resize;

	/*A constructor that initializes all the data from the received file */
	/*Saves the image under a new name in a folder in the project */
	public function __construct()
	{
		if (isset($_POST['submit'])) {
			$this->file = $_FILES['file'];
			$this->fileName = $_FILES['file']['name'];
			$this->fileTmpName = $_FILES['file']['tmp_name'];
			$this->fileSize = $_FILES['file']['size'];
			$this->fileError = $_FILES['file']['error'];
			$this->fileType = $_FILES['file']['type'];



			$this->fileExt = explode('.', $this->fileName);
			$this->fileActualExt = strtolower(end($this->fileExt));

			$this->allowed = array('png', 'jpeg');

			if (in_array($this->fileActualExt, $this->allowed)) {
				if ($this->fileError === 0) {
					if ($this->fileSize < 9999999) {

						$this->newFileName = uniqid('', true) . "." . $this->fileActualExt;
						$this->fileDestination = "upload/" . $this->newFileName;
						move_uploaded_file($this->fileTmpName, $this->fileDestination);
						$this->location = "upload/$this->newFileName";
						echo $this->calc();
					} else {
						echo '<script>alert("Your file is too big!")</script>';
						echo $this->backIndex();
					}
				} else {
					echo '<script>alert("ther was an error uploading your file!")</script>';
					echo $this->backIndex();
				}
			} else {

				echo '<script>alert("ERROR, you cannot upload files of this type!")</script>';
				echo $this->backIndex();
			}
		}
	}

	/*Returns the location of the folder where the image is saved */
	public function getLocation()
	{
		return $this->location;
	}
	/*A function that returns us to the home page after alert */
	public function backIndex()
	{
		echo '<script type="text/javascript">';
		echo 'window.location= "index.php"';
		echo '</script>';
	}
	/*Measures how much of each value of red green blue alpha there is
by going over each pixel in the image*/
	public function calc()
	{

		list($this->width, $this->height) = getimagesize($this->location);
		if ($this->fileActualExt == 'png') {
			/*imagecreatefrompng — Create a new image from file or URL*/
			$imgHand = imagecreatefrompng($this->location);
			if ($imgHand == false) {
				echo '<script>alert("ERROR, you cannot upload files of this type ,the conversion was not performed correctly!")</script>';

				echo $this->backIndex();
			}
		} else if ($this->fileActualExt == 'jpeg') {
			$imgHand = imagecreatefromjpeg($this->location);
			if ($imgHand == false) {
				echo '<script>alert("ERROR, you cannot upload files of this type ,the conversion was not performed correctly!")</script>';
				echo $this->backIndex();
			}
		} else {
			echo "The image was not converted correctly";
			die();
		}
		/*Resize image to reduce the running time*/
		//ceil returns the next highest integer value by rounding up num if necessary.
		$this->resize = $_POST['percent']; //100%
		debug_to_console($this->resize);
		$reWidth = ceil($this->width * $this->resize);
		$reHeight = ceil($this->height * $this->resize);
		$this->resized = imagecreatetruecolor($reWidth, $reHeight);
		imagecopyresampled(
			$this->resized,
			$imgHand,
			0,
			0,
			0,
			0,
			$reWidth,
			$reHeight,
			$this->width,
			$this->height
		);
		//matrix of pixels
		$this->pixelColorArray = array();

		for ($i = 0; $i < $reHeight; $i++) {
			$this->pixelColorArray[$i] = array();
			for ($j = 0; $j < 	$reWidth; $j++) {
				/*imagecolorat — Get the index of the color of a pixel*/
				$pixelColor = imagecolorat($this->resized, $j, $i);
				/*imagecolorsforindex  Get the colors for an pixel */

				$this->pixelColorArray[$i][$j] = imagecolorsforindex(
					$this->resized,
					$pixelColor
				);
				$this->intArray[] = $this->pixelColorArray[$i][$j]['red'];
				$this->intArray[] = $this->pixelColorArray[$i][$j]['green'];
				$this->intArray[] = $this->pixelColorArray[$i][$j]['blue'];
				//$this->intArray[] = $this->pixelColorArray[$i][$j]['alpha'];
				$this->arrayArray[] = $this->intArray;
				$this->intArray = (array)null;
			}
		}
		echo $this->calc2();
	}

	/*Saves color types from index to index*/
	public function calc2()
	{
		$index = 0;
		$this->Rangecounter = array(0);
		$this->counter = array();
		$fIndex = 0;
		for ($i = 0; $i < sizeof($this->arrayArray); $i++) {
			if ($this->arrayArray[$i] != $this->arrayArray[$index]) {
				$this->counter[] = $i - $fIndex;
				$fIndex = $i;
				$this->Rangecounter[] = $i;
				$index = $i;
			}
		}
		echo $this->sumRangecounter();
	}
	/*Unifies color values that appeared in different places in the image,  
		and save a total of how many color types there are*/
	public function sumRangecounter()
	{
		for ($i = 0; $i < sizeof($this->counter) - 1; $i++) {
			for ($j = $i + 1; $j < sizeof($this->counter) - 1; $j++) {
				//&& $this->counter[$j] != 0
				if ($this->arrayArray[$this->Rangecounter[$i]] == $this->arrayArray[$this->Rangecounter[$j]]) {
					$this->counter[$i] += $this->counter[$j];
					/*Every time I delete all the values I have collected
					 so that there will be less to run in the next iteration*/
					array_splice($this->counter, $j, 1);
					array_splice($this->Rangecounter, $j, 1);
				}
			}
		}
		$this->sumOfType = 0;
		for ($i = 0; $i < sizeof($this->counter); $i++) {
			$this->sumOfType += $this->counter[$i];
		}
		echo $this->find_5_Max_Index();
	}



	/*Returns the index of the highest value that is equal to the index it 
	receives but not in the same index and if not brings the next in line*/
	public function returnIndexLikeindexWithMaxCount($indexWithMaxCountArray)
	{
		$saveSmaller = 0;
		for ($i = 0; $i < sizeof($this->counter); $i++) {

			if ($this->counter[$i] > $saveSmaller && $this->counter[$i] <= $this->counter[$indexWithMaxCountArray] && $i  > $indexWithMaxCountArray) {
				$saveSmaller = $this->counter[$i];
				$MaxInIndex = $i;
			}
		}
		return $MaxInIndex;
	}
	/*Returns the index of the largest value*/
	public function returIndexWithMaxCount()
	{
		$temp = 0;
		for ($i = 0; $i < sizeof($this->counter); $i++) {
			if ($this->counter[$i] > $this->counter[$temp]) {
				$temp = $i;
			}
		}

		return $temp;
	}
	/*Looking for the values of the 5 biggest shows*/
	public function find_5_Max_Index()
	{
		if (sizeof($this->counter) >= 5) {
			$this->tempArray = array();
			$this->fivePopularRgb = array();
			$temp = $this->returIndexWithMaxCount();
			$this->tempArray[] = $temp;
			$this->fivePopularRgb[] = $this->arrayArray[$this->Rangecounter[$temp]];

			while (sizeof($this->tempArray) != 5) {
				$x = $this->returnIndexLikeindexWithMaxCount($temp);
				$this->tempArray[] = $x;
				$this->fivePopularRgb[] = $this->arrayArray[$this->Rangecounter[$x]];
				$temp = $x;
			}
			echo $this->calcPercentage();
		} else {
			echo '<script>alert("ERROR!")</script>';
			echo $this->backIndex();
		}
	}

	/*get color rgb*/
	public function getRgbColor()
	{
		return $this->fivePopularRgb;
	}
	/*Percentage calculation*/
	public function calcPercentage()
	{
		$this->fivePercentage = array();
		$this->fivePercentage[] = ($this->counter[$this->tempArray[0]] * 100) / $this->sumOfType;
		$this->fivePercentage[] = ($this->counter[$this->tempArray[1]] * 100) / $this->sumOfType;
		$this->fivePercentage[] = ($this->counter[$this->tempArray[2]] * 100) / $this->sumOfType;
		$this->fivePercentage[] = ($this->counter[$this->tempArray[3]] * 100) / $this->sumOfType;
		$this->fivePercentage[] = ($this->counter[$this->tempArray[4]] * 100) / $this->sumOfType;
	}

	/*get percent of most popular color*/
	public function getPercentage()
	{
		return $this->fivePercentage;
	}
}
