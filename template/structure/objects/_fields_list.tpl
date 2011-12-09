<?php

$TEMPLATE['frame'] = <<<END
<table>
    %fields%
</table>
END;

$TEMPLATE['separator'] = <<<END
    <tr><td colspan="2"></td></tr>
END;

$TEMPLATE['separator_text'] = <<<END
    <tr><td colspan="2"><h4>%title%</h4></td></tr>
END;

$TEMPLATE['field'] = <<<END
                        <tr>
                            <td>%field.name%</td>
                            <td>%field.value%</td>
                        </tr>    
END;

$TEMPLATE['field_list'] = <<<END
                        <tr>
                            <td>%field.name%</td>
                            <td>%field._value%</td>
                        </tr>
END;




?>