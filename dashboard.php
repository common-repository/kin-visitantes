<div class="wrap">
    <h2>Kin Visitantes</h2>
	<?php
	if (!empty($_POST['time_span'])) {
		$time_span=$_POST['time_span'];
	}
	else {
		$time_span=7;
	}
	$unix_time_span=strtotime('-' . $time_span . ' days');
	?>
	<h3><?php echo date('F j, Y',$unix_time_span) . ' - ' . date('F j, Y'); ?></strong></h3>
	<p>
	<form method="post" action="<?php echo str_replace('%7E','~',$_SERVER['REQUEST_URI']); ?>">
    	Date Range: <select name="time_span" id="filter-by-date" onchange="this.form.submit();">
			<option selected="selected" value="7">Last week</option>
			<option value="30">Last month</option>
			<option value="90">Last 90 Days</option>
			<option value="365">Last year</option>
		</select>
		<input type="submit" value="Filter" class="button">
	</form>
	</p>
    <h3>Pageviews</h3>
 	<script type="text/javascript" src="https://www.google.com/jsapi"></script>
    <script type="text/javascript">
    google.load("visualization","1",{packages:["corechart"]});
    google.setOnLoadCallback(drawChart);
    function drawChart() {
    	var data=google.visualization.arrayToDataTable([
        	['Date', 'Pageviews'],
        	<?php
        	$x=$time_span;
		    while($x>0) {
		    	$formated_month=date('m',strtotime('-' . $x . ' days'));
		    	$formated_day_of_month=date('d',strtotime('-' . $x . ' days'));
		    	$formated_year=date('Y',strtotime('-' . $x . ' days'));
			    $results=mysql_num_rows(mysql_query("SELECT * FROM wp_kin_visitantes WHERE FROM_UNIXTIME(date_time, '%m') = $formated_month AND FROM_UNIXTIME(date_time, '%d') = $formated_day_of_month AND FROM_UNIXTIME(date_time, '%Y') = $formated_year"));
		    	echo '[\'' . date('m/d/y',strtotime('-' . $x . ' days')) . '\', ' . $results . '],';
		    	$x--;
		    }
			?>  
        ]);
        var options={
        	legend:{position:'none'},
        	colors:['#2EA2CC'],
        	pointSize:5,
        	chartArea:{height:'100%',width:'100%'},
        	hAxis:{textPosition:'in'},
        	vAxis:{textPosition:'in'}
        };
        var chart=new google.visualization.AreaChart(document.getElementById('chart_div'));
        chart.draw(data,options);
    }
    </script>
    <div class="postbox" style="width:100%;">
 	   <div id="chart_div" style="width:100%; height:300px;"></div>
    </div>
	<div class="postbox-container" style="width:49%; padding:0 1% 0 0;">
		<h3>Sources</h3>
	    <table class="widefat">
			<thead>
				<tr>
					<th class="manage-column column-title">
						<span>Source</span>
					</th>
					<th class="manage-column column-date">
						<span>Pageviews</span>
					</th>
				</tr>
			</thead>
			<tbody>
			    <?php
			    $results=mysql_query("
			    	SELECT source, COUNT(source) FROM wp_kin_visitantes
			    		WHERE date_time >= $unix_time_span
			    		AND date_time <= UNIX_TIMESTAMP()
			    	GROUP BY source ORDER BY COUNT(source) DESC");
			    while($row=mysql_fetch_array($results)) {
			    	if ($row['source']=='') {$row['source']='Direct';}
					echo '<tr><td><a href="' . $row['source'] . '" target="_blank">'. $row['source'] . '</a></td><td>' . $row['COUNT(source)'] . '</td></tr>';
				}
				?>
			</tbody>
		</table>
	</div>
	<div class="postbox-container" style="width:49%; padding:0 0 0 1%;">
		<h3>Content</h3>
	    <table class="widefat">
			<thead>
				<tr>
					<th class="manage-column column-title">
						<span>Content</span>
					</th>
					<th class="manage-column column-date">
						<span>Pageviews</span>
					</th>
				</tr>
			</thead>
			<tbody>
			    <?php
			    $results=mysql_query("
			    	SELECT content, COUNT(content) FROM wp_kin_visitantes
			    		WHERE date_time >= $unix_time_span
			    		AND date_time <= UNIX_TIMESTAMP()
			    	GROUP BY content ORDER BY COUNT(content) DESC");
			    while($row=mysql_fetch_array($results)) {
					echo '<tr><td><a href="' . $row['content'] . '" target="_blank">'. $row['content'] . '</a></td><td>' . $row['COUNT(content)'] . '</td></tr>';
				}
				?>
			</tbody>
		</table>
	</div>
	<p style="clear:both;">&nbsp;</p>
	<div class="postbox-container" style="width:49%; padding:0 1% 0 0;">
		<h3>Languages</h3>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("visualization","1",{packages:["corechart"]});
		    google.setOnLoadCallback(drawChart);
		    function drawChart() {
			    var data=google.visualization.arrayToDataTable([
		        	['Language','Pageviews'],
		        	<?php
			        	$results=mysql_query("
			        		SELECT language, COUNT(language) FROM wp_kin_visitantes
			        			WHERE date_time >= $unix_time_span
			        			AND date_time <= UNIX_TIMESTAMP()
			        		GROUP BY language
			        		ORDER BY COUNT(language) DESC");
			        	while($row=mysql_fetch_array($results)) {
			        		if ($row['language']=='') {$row['language']='Unknown';}
				        	echo '[\'' . $row['language'] . '\',' . $row['COUNT(language)'] . '],';
				        }
				    ?>  
		        ]);
		        var options={
		        	is3D:true,
		        	colors:['#2ea2cc','#3c91cc','#4a81cc','#5870cc','#6660cc','#744fcc','#823fcc','#902fcc'],
		        	chartArea:{top:'10%',left:'10%',height:'100%',width:'100%'},
		        };
		        var chart=new google.visualization.PieChart(document.getElementById('piechart1'));
		        chart.draw(data,options);
		    }
		</script>
		<div class="postbox" style="width:100%;">
		    <div id="piechart1" style="height:300px; width:100%;"></div>
		</div>
	</div>
	<div class="postbox-container" style="width:49%; padding:0 0 0 1%;">
	    <h3>Browsers</h3>
		<script type="text/javascript" src="https://www.google.com/jsapi"></script>
		<script type="text/javascript">
			google.load("visualization","1",{packages:["corechart"]});
		    google.setOnLoadCallback(drawChart);
		    function drawChart() {
			    var data=google.visualization.arrayToDataTable([
		        	['Language','Pageviews'],
		        	<?php
			        	$results=mysql_query("
			        		SELECT browser, COUNT(browser) FROM wp_kin_visitantes
			        			WHERE date_time >= $unix_time_span
			        			AND date_time <= UNIX_TIMESTAMP()
			    			GROUP BY browser
			    			ORDER BY COUNT(browser) DESC");
			        	while($row=mysql_fetch_array($results)) {
			        		if ($row['browser']=='') {$row['browser']='Unknown';}
				        	echo '[\'' . $row['browser'] . '\',' . $row['COUNT(browser)'] . '],';
				        }
				    ?>  
		        ]);
		        var options={
		        	is3D:true,
		        	colors:['#2ea2cc','#3c91cc','#4a81cc','#5870cc','#6660cc','#744fcc','#823fcc','#902fcc'],
		        	chartArea:{top:'10%',left:'10%',height:'100%',width:'100%'},
		        };
		        var chart=new google.visualization.PieChart(document.getElementById('piechart2'));
		        chart.draw(data,options);
		    }
		</script>
		<div class="postbox" style="width:100%;">
		    <div id="piechart2" style="height:300px; width:100%;"></div>
		</div>
	</div>    
	<p>&nbsp;</p> 
    <table class="widefat">
		<thead>
			<tr>
				<th class="manage-column column-date" style="width:10%;">
					<form method="post" action="<?php echo str_replace('%7E','~',$_SERVER['REQUEST_URI']); ?>" id="sortbydate">
						<input type="hidden" name="sort" value="date_time DESC">
						<span style="color:#2EA2CC; font-weight:600; cursor:pointer;" onclick="document.getElementById('sortbydate').submit();">Date and Time</span>
					</form>
				</th>
				<th class="manage-column column-title" style="width:10%;">
					<form method="post" action="<?php echo str_replace('%7E','~',$_SERVER['REQUEST_URI']); ?>" id="sortbylanguage">
						<input type="hidden" name="sort" value="language">
						<span style="color:#2EA2CC; font-weight:600; cursor:pointer;" onclick="document.getElementById('sortbylanguage').submit();">Language</span>
					</form>
				</th>
				<th class="manage-column column-title" style="width:10%;">
					<form method="post" action="<?php echo str_replace('%7E','~',$_SERVER['REQUEST_URI']); ?>" id="sortbybrowser">
						<input type="hidden" name="sort" value="browser,os">
						<span style="color:#2EA2CC; font-weight:600; cursor:pointer;" onclick="document.getElementById('sortbybrowser').submit();">Browser and OS</span>
					</form>
				</th>
				<th class="manage-column column-title" style="width:10%;">
					<form method="post" action="<?php echo str_replace('%7E','~',$_SERVER['REQUEST_URI']); ?>" id="sortbyip">
						<input type="hidden" name="sort" value="ip">
						<span style="color:#2EA2CC; font-weight:600; cursor:pointer;" onclick="document.getElementById('sortbyip').submit();">IP Address</span>
					</form>
				</th>
				<th class="manage-column column-title" style="width:30%;">
					<form method="post" action="<?php echo str_replace('%7E','~',$_SERVER['REQUEST_URI']); ?>" id="sortbysource">
						<input type="hidden" name="sort" value="source">
						<span style="color:#2EA2CC; font-weight:600; cursor:pointer;" onclick="document.getElementById('sortbysource').submit();">Source</span>
					</form>
				</th>
				<th class="manage-column column-title" style="width:30%;">
					<form method="post" action="<?php echo str_replace('%7E','~',$_SERVER['REQUEST_URI']); ?>" id="sortbycontent">
						<input type="hidden" name="sort" value="content">
						<span style="color:#2EA2CC; font-weight:600; cursor:pointer;" onclick="document.getElementById('sortbycontent').submit();">Content</span>
					</form>
				</th>
			</tr>
		</thead>
		<tbody>
		    <?php
		    if (!empty($_POST['sort'])) {
			    $sort=$_POST['sort'];
			}
			else {
				$sort='date_time DESC';
			}
		    $results=mysql_query("
		    	SELECT * FROM wp_kin_visitantes
		    		WHERE date_time >= $unix_time_span
		    		AND date_time <= UNIX_TIMESTAMP()
			    ORDER BY $sort");
			while($row=mysql_fetch_array($results)) {
				if ($row['language']=='') {$row['language']='Unknown';}
				if ($row['browser']=='') {$row['browser']='Unknown';}
				if ($row['ip']=='') {$row['ip']='Unknown';}
				if ($row['source']=='') {$row['source']='Direct';}
				echo '
					<tr>
						<td>' . date('m/d/y h:ia',$row['date_time']) . '</td>
						<td>' . $row['language'] . '</td>
						<td>' . $row['browser'] . ' on ' . $row['os'] . '</td>
						<td>' . $row['ip'] . '</td>
						<td><a href="' . $row['source'] . '" target="_blank">' . $row['source'] . '</a></td>
						<td><a href="' . get_site_url() . $row['content'] . '">' . $row['content'] .'</a></td>
					</tr>
				';
			}
			?>
		</tbody>
	</table>
	<p>If you want to make a suggestion, want to learn more, or need support, visit <a href="http://www.kinwebdesign.com">kinwebdesign.com</a> or email <a href="mailto:sayhello@kinwebdesign.com">sayhello@kinwebdesign.com</a> for the latest scoop.</p>
</div>