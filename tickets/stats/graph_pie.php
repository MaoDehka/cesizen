<?php
################################################################################
# @Name : ./stats/graph_pie.php
# @Description : display pies statistics
# @Call : /stat.php
# @Parameters : unit, values, names, libchart
# @Author : Flox
# @Create : 06/10/2012
# @Update : 03/02/2022
# @Version : 3.2.20
################################################################################
?>
<script type="text/javascript">
	$(function () {
    var chart;
    $(document).ready(function() {
		
		// Build the chart
        chart = new Highcharts.Chart({
            chart: {
                renderTo: '<?php echo $container; ?>',
                plotBackgroundColor: null,
                plotBorderWidth: null,
                plotShadow: false,
				backgroundColor:'<?php echo $bgc; ?>'
            },
			credits: {
				enabled: false
			},
            title: {
                <?php
                    //modify color on dark theme
                    if($ruser['skin']=='skin-4') {echo "style: {color: '#99a0a5'},";}
                ?>
                text: "<?php echo $libchart; ?>"
            },
            tooltip: {
        	    pointFormat: '{series.name}: <b>{point.percentage:.2f}%</b>'
            },
            plotOptions: {
                pie: {
                    allowPointSelect: true,
                    cursor: 'pointer',
                    dataLabels: {
						style: {
                            width: '200px'
						},
                        enabled: true,
                        color: '#000000',
                        connectorColor: '#ccc',
                        formatter: function() {
                            return '<b>'+ this.point.name +'</b> : '+ Math.round(this.percentage) +'% ('+ this.point.y +' <?php echo $unit; ?>)';
                        }
                    }
                }
            },
            series: [{
                type: 'pie',
                name: '<?php echo T_('Répartition'); ?>',
                data: [
                   <?php
					for($i=0;$i<sizeof($values);$i++) 
					{ 
						$k=sizeof($values);
						$k=$k-1;
						if ($i==$k) echo "['$xnom[$i]', $values[$i]]"; else echo "['$xnom[$i]', $values[$i]],";
					} 
					?>
                ]
            }]
        });
    });
    
});
</script>