# php-image-to-ascii
Create ascii art of a image

Outputs to either plan text or html wrapped chars


## How to use
```PHP
include('ascii.php');

$Ascii = new Ascii("images/input.jpg");

//outputs in browser
$Ascii->create_ascii_to_html(); 

//or use this to save to file
$Ascii->create_ascii_to_file(); 
```


## Example
Input image

![Input image](images/input.jpg)



Output ascii

View as ![plain text](images/ascii.txt)

![output image](images/output.jpg)
