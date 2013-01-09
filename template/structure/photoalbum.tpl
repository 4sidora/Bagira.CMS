%core.include_templ(header)%


<div class="wrapper"> 
<div class="container">
	<div id="page">
    
    <div id="column1">
    	 <form action="/search" method="post">
            <input type="text" id="search" name="words"  value="Поиск"/>
    	</form>
        %structure.menu(photogalmenu, 1, 1570)%

        
        <h3>%structure.getProperty(name, 1578)% </h3>
        %structure.getProperty(content, 1578)%
          
        
    </div>
    
	 <div id="column2">
    	<h1>%h1%</h1>
        <div class="wrappercut">
        %content%
          
     </div>
     </div>
<div class="clear"></div> 
</div>
</div>
</div>


%core.include_templ(footer)%