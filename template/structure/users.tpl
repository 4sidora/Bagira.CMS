%core.include_templ(header)%


<div class="wrapper"> 
<div class="container">
	<div id="page">
    
    <div id="column1">
        <form action="/search" method="post">
                <input type="text" id="search" name="words"  value="Поиск"/>
        </form>
        %structure.menu(second, 1, 1620)%
    </div>
	 
    <div id="column2">
   
    	%content%
       
     </div> 
     <div class="clear"></div>      
</div>
</div>
</div>


%core.include_templ(footer)%