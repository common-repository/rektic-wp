/**
 * Rektic WP
 *
 * This file handles plugins communication with server's backend through wordpress ajax endpoints
 * from within the editor
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */

jQuery(document)
	.ready(function($){
		let currentEditor = null,
			nonce         = "",
			pdfId         = "",
			seoInfo       = null;
		setTimeout(function(){
			let content = tinyMCE?.activeEditor?.getContent();
			if(content === undefined){
				showError("No support", "Rektic supports only classic editor. Please regenerate the article.");
			}
			else{
				nonce = $("#rek-meta-nonce")
					.val();
				currentEditor = tinyMCE.activeEditor;

				$(".rektic-metabox-loader")
					.hide();
				$(".rektic-metabox")
					.show();
				listenToSaveEvent();
				updateSeoScore();
				updatePlagia();
			}
		}, 3000);

		let checkedSaving = false;

		function listenToSaveEvent(){
			const unsubscribe = wp.data.subscribe(function(){
				let select = wp.data.select("core/editor");
				const isSavingPost = select.isSavingPost();
				const isAutosavingPost = select.isAutosavingPost();
				const didPostSaveRequestSucceed = select.didPostSaveRequestSucceed();
				if((isSavingPost || isAutosavingPost) && didPostSaveRequestSucceed){

					if(checkedSaving){
						checkedSaving = false;
						let data = {
							nonce:   nonce,
							action:  "rektic_save_article",
							id:      rekticArticleMeta.id,
							content: currentEditor.getContent(),
						};

						jQuery.ajax({
										type:     "post",
										dataType: "json",
										url:      rektic.ajaxurl,
										data:     data,
										success:  function(response){
											if(response.type === "success"){
												mkNoti("Saved!", "Article has been saved", {
													status: "success",
												});
												updateSeoScore();
											}
											else{
												showError();
											}
										},
										complete: () => {
										},
									});
						unsubscribe();
						listenToSaveEvent();
					}
					else{
						checkedSaving = true;
					}
				}
			});

		}

		const regenBtn = $("#rektic-generate-seo");
		const plagiaBtn = $("#rektic-plagia-gen");
		const plagiaDlBtn = $("#rektic-plagia-dl");

		function updateSeoScore(regen = false){
			let data = {
				nonce:   nonce,
				action:  "rektic_get_seo_score",
				id:      rekticArticleMeta.id,
				content: currentEditor.getContent(),
			};

			regenBtn.prop("disabled", true);

			if(regen){
				data.mustRegen = true;
			}

			jQuery.ajax({
							type:     "post",
							dataType: "json",
							url:      rektic.ajaxurl,
							data:     data,
							success:  function(response){
								if(response.type === "success"){
									seoInfo = response.info;
									const scoreHolder = $("#rektic-seo-score");

									scoreHolder.text(response.score + "%");
									if(response.score < 50){
										scoreHolder.addClass("text-danger");
									}
									else{
										scoreHolder.removeClass("text-danger");
									}
								}
								else{
									showError();
								}
							},
							complete: () => {
								regenBtn.prop("disabled", false);
								cxDialog.close();
							},
						});
		}

		function updatePlagia(regen = false){
			let data = {
				nonce:   nonce,
				action:  "rektic_request_plagia",
				id:      rekticArticleMeta.id,
				content: currentEditor.getContent(),
			};

			plagiaBtn.prop("disabled", true);
			plagiaDlBtn.prop("disabled", true);

			if(regen){
				data.mustRegen = true;
			}

			jQuery.ajax({
							type:     "post",
							dataType: "json",
							url:      rektic.ajaxurl,
							data:     data,
							success:  function(response){
								if(response.type === "success"){
									if(response.upload != null){
										pdfId = response.upload;
										plagiaDlBtn.show();
									}
									else{
										pdfId = "";
										plagiaDlBtn.hide();
									}
								}
								else{
									showError();
								}
							},
							complete: () => {
								plagiaBtn.prop("disabled", false);
								plagiaDlBtn.prop("disabled", false);
								cxDialog.close();
							},
						});
		}

		regenBtn.on("click", () => {
			cxDialog(loaderAlert);
			updateSeoScore(true);
		});
		plagiaBtn.on("click", () => {
			cxDialog(loaderAlert);
			updatePlagia(true);
		});
		$("#rektic-generate-seo-report")
			.on("click", () => {
				let accHeader = $('<div>')
					.addClass('acc-head')
				let listOfGood = $("<div></div>")
					.addClass("acc-body");
				let listOfBad = $("<div></div>")
					.addClass("acc-body");
				let listOfOkay = $("<div></div>")
					.addClass("acc-body");
				let reg = /{.*?}/g;
				Object.entries(seoInfo)
					  .forEach(entry => {
						  const [key, value] = entry;
						  let title = $("<strong>", {text: key + ": "});

						  function replacer(text){
							  text = text.replace(/{/, "");
							  text = text.replace(/}/, "");
							  return value.vars[text];
						  }

						  value.msg = value.msg.replace(new RegExp(reg, "gi"), replacer);
						  let iconColor = "color: blue";
						  if(value.status === -1){
							  iconColor = "color: red";
						  }
						  else if(value.status === 1){
							  iconColor = "color: green";
						  }

						  let icon = $("<span class=\"dashicons dashicons-controls-play\" style='" + iconColor + "'></span>");
						  let elt = $("<p>", {
							  text: value.msg,
						  })
							  .prepend(title)
							  .prepend(icon);
						  if(value.status === -1){
							  listOfBad.append(elt);
						  }
						  else if(value.status === 1){
							  listOfGood.append(elt);
						  }
						  else{
							  listOfOkay.append(elt);
						  }
					  });

				let problemsHeader = accHeader.clone()
											  .text('Problems:')
				let improvementsHeader = accHeader.clone()
												  .text('Improvements:')
				let alreadyHeader = accHeader.clone()
											 .text('Already Optimized:')

				let display = $('<div>')
					.addClass('rektic-keywords-list ')
					.append(problemsHeader)
					.append(listOfBad)
					.append(improvementsHeader)
					.append(listOfOkay)
					.append(alreadyHeader)
					.append(listOfGood)

				const opts = {
					info:  display[0].outerHTML,
					title: "SEO Suggestions",
				};

				cxDialog(opts);
				$(document)
					.ready(function(){
						$('.acc-head')
							.click(function(){
								$(this)
									.next()
									.slideToggle(500);
								$(this)
									.toggleClass('active');
							})
					})
			});

		plagiaDlBtn
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}
				cxDialog(loaderAlert);

				$this.prop("disabled", true);

				let data = {
					nonce:  nonce,
					action: "rektic_download_plagia",
					id:     rekticArticleMeta.id,
					pdfId:  pdfId,
				};

				jQuery.ajax({
								type: "post",
								url:  rektic.ajaxurl,
								data: data,

								cache: false,

								xhr:      function(){

									var xhr = new XMLHttpRequest();

									xhr.onreadystatechange = function(){

										if(xhr.readyState === 2){

											if(xhr.status === 200){

												xhr.responseType = "blob";

											}
											else{

												xhr.responseType = "text";

											}

										}

									};

									return xhr;

								},
								success:  function(response){
									const url = window.URL.createObjectURL(new Blob([response]));
									const link = document.createElement("a");
									link.href = url;
									link.setAttribute("download", `Plagiarism Report - Rektic.pdf`);
									document.body.appendChild(link);
									link.click();
									link.parentNode.removeChild(link);
								},
								complete: () => {
									$this.prop("disabled", false);
									cxDialog.close();
								},
							});
			});

		$("#rektic-generate-rewrite")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}

				const selectedText = currentEditor.selection.getContent({format: "text"});
				if(selectedText !== ""){
					cxDialog(loaderAlert);
					$this.prop("disabled", true);

					let data = {
						nonce:    nonce,
						action:   "rektic_generate_rewrite",
						language: rekticArticleMeta.lang,
						text:     selectedText,
					};

					jQuery.ajax({
									type:     "post",
									dataType: "json",
									url:      rektic.ajaxurl,
									data:     data,
									success:  function(response){
										if(response.type === "success"){
											currentEditor.selection.setContent(response.text);
										}
										else{
											showError();
										}
									},
									complete: () => {
										$this.prop("disabled", false);
										cxDialog.close();
									},
								});
				}
				else{
					showError("Empty content", "Please select the text you want to rewrite.");
				}
			});

		$("#rektic-generate-more")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}

				let node     = currentEditor.selection.getNode(),
					nodeText = node.innerText;

				$this.prop("disabled", true);

				cxDialog(loaderAlert);

				let data = {
					nonce:    nonce,
					action:   "rektic_generate_more",
					id:       rekticArticleMeta.id,
					language: rekticArticleMeta.lang,
					text:     nodeText,
				};

				jQuery.ajax({
								type:     "post",
								dataType: "json",
								url:      rektic.ajaxurl,
								data:     data,
								success:  function(response){
									if(response.type === "success"){
										$(node)
											.append(response.text);
									}
									else{
										showError();
									}
								},
								complete: () => {
									$this.prop("disabled", false);
									cxDialog.close();
								},
							});

			});

		$("#rektic-generate-intro")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}

				cxDialog(loaderAlert);

				let title = wp.data.select("core/editor")
							  .getCurrentPost().title;

				$this.prop("disabled", true);

				let data = {
					nonce:    nonce,
					action:   "rektic_generate_intro",
					id:       rekticArticleMeta.id,
					language: rekticArticleMeta.lang,
					text:     title,
				};

				jQuery.ajax({
								type:     "post",
								dataType: "json",
								url:      rektic.ajaxurl,
								data:     data,
								success:  function(response){
									if(response.type === "success"){
										let editorContent = currentEditor.getContent();
										const content = $("<p></p>")
											.text(response.text);
										currentEditor.setContent($(content)[0].outerHTML + editorContent);
									}
									else{
										showError();
									}
								},
								complete: () => {
									cxDialog.close();
									$this.prop("disabled", false);
								},
							});

			});

		$("#rektic-top-ranked")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}

				cxDialog(loaderAlert);

				$this.prop("disabled", true);

				let data = {
					nonce:  nonce,
					action: "rektic_suggested-links",
					id:     rekticArticleMeta.id,
				};

				jQuery.ajax({
								type:     "post",
								dataType: "json",
								url:      rektic.ajaxurl,
								data:     data,
								success:  function(response){
									if(response.type === "success"){
										let listOfWebsites = $("<div></div>")
											.addClass("rektic-links-list");
										for(const link of response.links){
											let elt = $("<a>", {
												text:  link.title,
												title: link.website,
												href:  link.link,
											});
											elt.prepend(
												$("<img alt=\"\" class='rektic-link-favicon' style=\"width: 25px; height:" + " 25px;\" src='" +
												  link.favicon + "'>"));
											listOfWebsites.append(elt);
										}

										const opts = {
											info:      listOfWebsites[0].outerHTML,
											title:     "Related Websites",
											maskClose: true,
										};

										cxDialog(opts);

									}
									else{
										showError();
									}
								},
								complete: () => {
									cxDialog.close();
									$this.prop("disabled", false);
								},
							});
			});

		$("#rektic-top-seo")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}

				cxDialog(loaderAlert);

				$this.prop("disabled", true);

				let data = {
					nonce:  nonce,
					action: "rektic_suggested-seo",
					id:     rekticArticleMeta.id,
				};

				jQuery.ajax({
								type:     "post",
								dataType: "json",
								url:      rektic.ajaxurl,
								data:     data,
								success:  function(response){
									if(response.type === "success" && response.keywords.length > 0){
										let listOfWebsites = $("<div></div>")
											.addClass("rektic-keywords-list");
										for(const keyword of response.keywords){
											let elt = $("<p>", {
												text: keyword,
											});
											elt.prepend($("<span class=\"dashicons dashicons-controls-play\"></span>"));
											listOfWebsites.append(elt);
										}

										const opts = {
											info:      listOfWebsites[0].outerHTML,
											title:     "Associated searches",
											maskClose: true,
										};

										cxDialog(opts);

									}
									else{
										response.type === "success"  ? showError( 'No results', 'There are no results fitting your request. Please try again after writing more content.' ) :showError(  ) ;
									}
								},
								complete: () => {
									$this.prop("disabled", false);
									cxDialog.close();
								},
							});
			});

		$("#rektic-top-secondary")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}

				cxDialog(loaderAlert);

				$this.prop("disabled", true);

				let data = {
					nonce:  nonce,
					action: "rektic_suggested-keywords",
					id:     rekticArticleMeta.id,
				};

				jQuery.ajax({
								type:     "post",
								dataType: "json",
								url:      rektic.ajaxurl,
								data:     data,
								success:  function(response){
									if(response.type === "success"){
										let listOfWebsites = $("<div></div>")
											.addClass("rektic-keywords-list");
										for(const keyword of response.keywords){
											let word = keyword;
											if(typeof keyword === "object"){
												word = keyword.spell;
												if(word == null){
													continue;
												}
											}
											let elt = $("<p>", {
												text: word,
											});
											elt.prepend($("<span class=\"dashicons dashicons-controls-play\" ></span>"));
											listOfWebsites.append(elt);
										}

										const opts = {
											info:      listOfWebsites[0].outerHTML,
											title:     "Secondary keyword suggestions",
											maskClose: true,
										};

										cxDialog(opts);

									}
									else{
										showError();
									}
								},
								complete: () => {
									cxDialog.close();
									$this.prop("disabled", false);
								},
							});
			});

		$("#rektic-top-secondary-deap")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}

				if(rekticUser.plan !== 'company' && rekticUser.plan !== 'pro'){
					cxDialog(upgradeAlert);
				}
				else{
					cxDialog(loaderAlert);

					$this.prop("disabled", true);

					let data = {
						nonce:  nonce,
						action: "rektic_suggested-keywords",
						id:     rekticArticleMeta.id,
						advanced:     true,
					};

					jQuery.ajax({
									type:     "post",
									dataType: "json",
									url:      rektic.ajaxurl,
									data:     data,
									success:  function(response){
										if(response.type === "success"){

											let header = $("<div>")
												.addClass("keywords-header")
												.append($("<div>", {
													html: "<strong>Country:</strong> " + response.country,
												}))
												.append($("<div>", {
													html: "<strong>Date:</strong> " + response.date,
												}));

											let listOfArticles = $("<table style=\"width:100%\">\n" + "\t<thead>\n" + "\t\t<tr>\n" +
																   "\t\t\t<th scope=\"col\">Keyword</th>\n" +
																   "\t\t\t<th scope=\"col\">Volume</th>\n" +
																   "\t\t\t<th scope=\"col\">CPC Min</th>\n" +
																   "\t\t\t<th scope=\"col\">CPC Max</th>\n" +
																   "\t\t\t<th scope=\"col\">Difficulty</th>\n" + "\t\t</tr>\n" + "\t</thead>\n" +
																   "\t<tbody>\n" + "\t</tbody>\n" + "</table>")
												.addClass("rektic-articles-list");
											for(const article of response.keywords){
												let td0 = $("<td>", {text: article.keyword});
												let td1 = $("<td>", {text: article.last_search_volume});
												let td2 = $("<td>", {text: article.low_top_of_page_bid + "$"});
												let td3 = $("<td>", {text: article.high_top_of_page_bid + "$"});
												let td4 = $("<td>", {text: article.competition_index + "%"});

												let tr = $("<tr>")
													.append(td0)
													.append(td1)
													.append(td2)
													.append(td3)
													.append(td4);

												listOfArticles.append(tr);
											}

											let result = $("<div>")
												.append(header)
												.append(listOfArticles);

											const opts = {
												info:      result[0].outerHTML,
												title:     "Secondary keyword suggestions - InDepth",
												maskClose: true,
											};

											cxDialog(opts);

										}
										else{
											showError();
										}
									},
									complete: () => {
										cxDialog.close();
										$this.prop("disabled", false);
									},
								});
				}
			});

		$("#rektic-top-most-asked")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}
				cxDialog(loaderAlert);

				$this.prop("disabled", true);

				let data = {
					nonce:  nonce,
					action: "rektic_suggested-most-asked",
					id:     rekticArticleMeta.id,
				};

				jQuery.ajax({
								type:     "post",
								dataType: "json",
								url:      rektic.ajaxurl,
								data:     data,
								success:  function(response){
									if(response.type === "success"){
										let listOfWebsites = $("<div></div>")
											.addClass("rektic-keywords-list");
										for(const keyword of response.keywords){
											let elt = $("<p>", {
												text: keyword,
											});
											elt.prepend($("<span class=\"dashicons dashicons-warning\" ></span>"));
											listOfWebsites.append(elt);
										}

										const opts = {
											info:  listOfWebsites[0].outerHTML,
											title: "People also ask",
										};

										cxDialog(opts);

									}
									else{
										showError();
									}
								},
								complete: () => {
									$this.prop("disabled", false);
									cxDialog.close();
								},
							});
			});

		$("#rektic-top-articles")
			.on("click", (e) => {
				e.preventDefault();
				let $this = $(e.target);

				if(currentEditor === null){
					return;
				}
				if(rekticUser.plan !== 'company' && rekticUser.plan !== 'pro'){
					cxDialog(upgradeAlert);
				}
				else{
					cxDialog(loaderAlert);

					$this.prop("disabled", true);

					let data = {
						nonce:  nonce,
						action: "rektic_suggested-links",
						id:     rekticArticleMeta.id,
						advanced:     true,
					};

					jQuery.ajax({
									type:     "post",
									dataType: "json",
									url:      rektic.ajaxurl,
									data:     data,
									success:  function(response){
										if(response.type === "success"){

											let listOfArticles = $("<table style=\"width:100%\">\n" + "\t<thead>\n" + "\t\t<tr>\n" +
																   "\t\t\t<th scope=\"col\">Position</th>\n" +
																   "\t\t\t<th scope=\"col\">Google result</th>\n" +
																   "\t\t\t<th scope=\"col\">Words count</th>\n" +
																   "\t\t\t<th scope=\"col\">SEO Score</th>\n" + "\t\t</tr>\n" + "\t</thead>\n" +
																   "\t<tbody>\n" + "\t</tbody>\n" + "</table>")
												.addClass("rektic-articles-list");
											let i = 1;
											for(const article of response.links){
												if(article.metaDataSeo !== undefined && article.metaDataSeo !== null && article.metaDataSeo.size !==
												   undefined && article.metaDataSeo.size !== null && article.metaDataSeo.seo_score !== undefined &&
												   article.metaDataSeo.seo_score !== null){

													let articleHTML = "<p><img alt=\"\" src=\"$ICON$\" />\n" + "$TITLE$</p>\n" + "\n" +
																	  "<p>$DESCRIPTION$</p>\n" + "\n" +
																	  "<p><a href=\"$LINK$\" rel=\"noopener noreferrer\"" +
																	  " target=\"_blank\">$LINKSHORT$</a></p>";

													articleHTML = articleHTML.replace(/\$ICON\$/g, article.favicon);
													articleHTML = articleHTML.replace(/\$TITLE\$/g, article.title);
													articleHTML = articleHTML.replace(/\$DESCRIPTION\$/g, article.meta);
													articleHTML = articleHTML.replace(/\$LINK\$/g, article.link);
													articleHTML = articleHTML.replace(/\$LINKSHORT\$/g, article.website);

													let elt = $("<div>")
														.addClass("rektic-article-preview")
														.html(articleHTML);

													let td0 = $("<td>", {text: i});
													let td1 = $("<td>")
														.append(elt);
													let td2 = $("<td>", {
														text: (article.metaDataSeo.size) + " Words",
													});
													let td3 = $("<td>", {
														text: (article.metaDataSeo.seo_score) + "%",
													});

													let tr = $("<tr>")
														.append(td0)
														.append(td1)
														.append(td2)
														.append(td3);

													listOfArticles.append(tr);
													i++;
												}
											}

											const opts = {
												info:  listOfArticles[0].outerHTML,
												title: "Google results",
											};

											cxDialog(opts);

										}
										else{
											showError();
										}
									},
									complete: () => {
										$this.prop("disabled", false);
										cxDialog.close();
									},
								});
				}
			});
	});
