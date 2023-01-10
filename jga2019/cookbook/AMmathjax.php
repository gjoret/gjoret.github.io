<?php if (!defined('PmWiki')) exit();
/*
ASCIIMath - Display MathML rendered ascii formula into PmWiki 2.x pages
Using MahJax
Author: Massimiliano Vessi
Email: angerangel@gmail.com
 */
#version
$RecipeInfo['MathJaxASCCIMath']['Version'] = '20131129';

#header to include with link to last version of MathJax
#$HTMLHeaderFmt['MathJax'] =   '<script async type="text/javascript"
#src="http://cdn.mathjax.org/mathjax/latest/MathJax.js?config=TeX-MML-AM_HTMLorMML-full">
#</script>' ;

$HTMLHeaderFmt['MathJax'] =   '<script async type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/mathjax/2.7.1/MathJax.js?config=TeX-MML-AM_HTMLorMML-full">
</script>' ;

#Markup sobstitution
Markup('{$', '>[=', '/\\{\\$(.*?)\\$\\}/e', "Keep('`'.PSS('$1').'`')");

#Added button, if is missing add  the image http://www.pmichaud.com/pmwiki/pub/guiedit/math.gif .
#to your your folder  pmwiki/pub/guiedit/
SDV($GUIButtons['math'], array(1000, '{$ ', ' $}', '+-sqrt(n)',

  '$FarmPubDirUrl/guiedit/math.gif"$[Math formula (ASCIIMathML)]"'));
