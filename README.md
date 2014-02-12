# CI-Twig

A [CodeIgniter](http://codeigniter.com) library that enables the use of [Twig](http://twig.sensiolabs.org/)

## Installation

You must have Twig installed. See [Twig's how-to](http://twig.sensiolabs.org/doc/intro.html#installation) on how to do that.

Clone this repository. Then copy over all files to their respective directories.

## Usage

This library aims to completely replace CodeIgniter's default Output library (as used by `$this->load->view()`).

Simply load the library

    $this->load->library('twig');
    
Then when you wish to output a view call it with

    $this->twig->view('myview');
    
The `view` and `render` functions behave the same way as CI's default Output library.

## JSON

This library also comes with a few convenience methods (and a default template) to help with the output of JSON.

A shotgun approach:

    $this->twig->json(array('message' => 'json output here'));
    
Some helper methods:

    // failing
    $this->twig->fail("There was an error");
    
    // succeeding
    $this->twig->success();
    
## Errors

A convinience method to output a standard error template is provided. See config file to change the default template.

    $this->twig->error("There was an error");
    
## Config

See the included config file to change any defaults.