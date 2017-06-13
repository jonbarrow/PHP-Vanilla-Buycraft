{% extends "templates/base.twig" %}

{% block content %}

<center id="header">
	<p>Logged in as: <i id="result-name" style="font-weight:bolder;"></i></p>
	<img id="result-img" src="">
	<button class="btn btn-info nameUpdate" onclick="nameEnter()">Update Username</button>
</center>

<div class="row center well">
	{% if data.sections and not data.sections is empty %}
		<div class="donation-options col-md-9">
			<div class="tabs">
				<ul class="nav nav-tabs nav-justified">
					{% for section in data.sections %}
						{% if loop.first %}
							{% set active = 'active' %}
						{% else %}
							{% set active = '' %}
						{% endif %}
						<li class="{{ active }}"><a data-toggle="tab" href="#store-option-{{section.name}}">{{section.display}}</a></li>
					{% endfor %}
				</ul>
			</div><br>
			<div class="options tab-content">
				{% for section in data.sections %}
					{% if loop.first %}
						{% set active = 'active' %}
					{% else %}
						{% set active = '' %}
					{% endif %}
					<div class="row {{section.name}} tab-pane fade in {{ active }}" id="store-option-{{section.name}}">
						{% if attribute(data.options, section.name) and not attribute(data.options, section.name) is empty %}
							{% for item in attribute(data.options, section.name) %}
								<div class="col-md-4 col-sm-6 col-xs-12">
									<div class="card card-pricing card-raised">
										<div class="card-content">
											<h6 class="category">{{ item.name }}</h6>
											<div class="icon icon-rose center-block">
												<img class="img-responsive img-circle img-center" style="max-width:50%;max-height:50%;" src="{{ item.icon }}">
											</div>
											<h3 class="card-title">${{ item.price }}</h3>
											<p class="card-description">
												{{ item.desc }}
											</p>
											<form action="/checkout" method="post" autocomplete="off">
											    <label for="itemID" style="display:none">
											     	<input type="text" name="itemID" value="{{ item.id }}">
											    </label>
											    <button class="btn sub-btn btn-rose btn-simple">Purchase</button>
											</form>
										</div>
									</div>
								</div>
							{% endfor %}
						{% endif %}
					</div>
				{% endfor %}
			</div>
		</div>

		<div class="col-md-3">
			<div class="well">
				<div class="center panel panel-primary">
					<div class="panel-heading text-float-left" style="color: #999999;">Top Donor</div>
					<div class="panel-body text-float-left">
						<div class="center">
							{% if data.donors.top %}
								<font color='#FF5555'>{{ data.donors.top.name }}</font> | <font color='#55FF55'>${{ data.donors.top.total }}</font><br>
								<img src='https://visage.surgeplay.com/full/192/{{ data.donors.top.name }}'>
							{% else %}
								<font color='#FF5555'>No donations!</font>
							{% endif %}
						</div>
					</div>
				</div>
				<div class="center panel panel-primary">
					<div class="panel-heading text-float-left" style="color: #999999;">Donation Progress</div>
					<div class="panel-body text-float-left">
						<div class="col-xs-12 col-sm-12 progress-container">
						    <div class="progress">
							  	<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="60" aria-valuemin="0" aria-valuemax="100" style="width: 60%;">
							    	60%
							  	</div>
							</div>
						</div>
					</div>
				</div>
				<div class="center panel panel-primary">
					<div class="panel-heading text-float-left" style="color: #999999;">Recent Donations</div>
					<div class="panel-body text-float-left">
						{% if data.donors.list and not data.donors.list is empty %}
							{% for player in data.donors.list %}
							<div class="donations-donor-recent">
								<img src='https://visage.surgeplay.com/head/48/{{ player.name }}'>
								<p>
							    	<font class="color-red" size=3><small>{{ player.name }}</small></font><br>
							    	<font class="color-blue"><small>{{ player.product }} : ${{ player.price }}</small></font>
							  	</p>
							</div>
							{% endfor %}
						{% else %}
							<font color='#FF5555'>No donations!</font>
						{% endif %}
					</div>
				</div>
			</div>
		</div>
	{% endif %}
</div>

{% endblock %}