%core.include_templ(header)%


<div class="wrapper"> 
<div class="container">
	<div id="page">
    
    <div id="column1">
    	 <form action="/search" method="post">
            <input type="text" id="search" name="words"  value="Поиск"/>
    	</form>

        %voting.objList(all, default, 1)%

        %subscription.form()%

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