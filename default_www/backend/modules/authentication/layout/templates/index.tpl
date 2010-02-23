{include:file='{$BACKEND_CORE_PATH}/layout/templates/head.tpl'}
<body id="login">
	{option:debug}<div id="debugnotify">Debug mode</div>{/option:debug}
	<table border="0" cellspacing="0" cellpadding="0" id="loginHolder">
		<tr>
			<td>
				<div id="loginBox" {option:hasError}class="hasError"{/option:hasError}>
					{option:hasError}
					<div id="loginError">
						<div class="errorMessage singleMessage">
							<p>{$errInvalidUsernamePasswordCombination}</p>
						</div>
					</div>
					{/option:hasError}


					<div id="loginBoxTop">
						<h2>{$SITE_TITLE}</h2>
					</div>

					{form:authenticationIndex}
						<div class="horizontal">
							<div id="loginFields">
								<p>
									<label for="backendUsername">{$lblUsername|ucfirst}</label>
									{$txtBackendUsername} {$txtBackendUsernameError}
								</p>

								<p>
									<label for="backendPassword">{$lblPassword|ucfirst}</label>
									{$txtBackendPassword} {$txtBackendPasswordError}
								</p>
							</div>
							<p class="spacing">
								<input id="login" class="inputButton button mainButton" type="submit" name="login" value="{$lblSignIn|ucfirst}" />
							</p>
						</div>
					{/form:authenticationIndex}
					
						<ul id="loginNav">
							<li><a href="http://userguide.fork-cms.be">{$lblUserguide|ucfirst}</a></li>
							<li><a href="http://docs.fork-cms.be">{$lblDeveloper|ucfirst}</a></li>
							<li><a href="#" id="forgotPasswordLink" class="toggleBalloon" rel="forgotPasswordHolder">{$msgForgotPassword}</a></li>
						</ul>
					</div>
				</div>
				<div id="forgotPasswordHolder" class="balloon"{option:!showForm} style="display: none;"{/option:!showForm}>
					<div id="forgotPassword">
						<div class="balloonTop">&nbsp;</div>
						<p>{$msgForgotPasswordHelp}</p>
						{form:forgotPassword}
							<div class="oneLiner">
								<p><label for="backendEmail">{$lblEmail|ucfirst}</label></p>
								<p>{$txtBackendEmail}</p>
								<p>
									<input id="send" class="inputButton button mainButton" type="submit" name="send" value="{$lblSend|ucfirst}" />
								</p>
							</div>

							{option:txtBackendEmailError}
							<div class="errorMessage singleMessage">
								<p>{$txtBackendEmailError}</p>
							</div>
							{/option:txtBackendEmailError}

							{option:hasForgotpasswordError}
							<div class="errorMessage singleMessage">
								<p>{$msgLoginFormForgotPasswordError}</p>
							</div>
							{/option:hasForgotpasswordError}

							{option:isForgotpasswordSuccess}
							<div class="successMessage singleMessage">
								<p>{$msgLoginFormForgotPasswordSuccess}</p>
							</div>
							{/option:isForgotpasswordSuccess}
						{/form:forgotPassword}
					</div>
				</div>
			</td>
		</tr>
	</table>
{include:file='{$BACKEND_CORE_PATH}/layout/templates/footer.tpl'}