<div id="column1">
    	
        <form action="/search" method="post">
                <input type="text" id="search" name="words"  value="Поиск"/>
        </form>
       
       
        %structure.menu(novoe, 1, 1624)% 
        
        %structure.menu(catalog, 2, 345)% 
        
        
        
         %voting.objList(all, default, 1, random)%
        
        <h3>%structure.getProperty(name, 1578)% </h3>
        %structure.getProperty(content, 1578)%


        %subscription.form()%
    
    </div>