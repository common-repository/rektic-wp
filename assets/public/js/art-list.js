/**
 * Rektic WP
 *
 * This file controls the logic in the Article List page
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */
jQuery(document)
	.ready(function($){

		$(".rektic-export")
			.on("click", (e) => {
				let $this = $(e.target)
						.closest(".rektic-export"),
					id    = $this
						.data("id");

				let data = {
					nonce : rekticArtList.nonce,
					action: "rektic_get_article",
					id    : id,
				};

				cxDialog(loaderAlert);

				jQuery.ajax({
								type    : "post",
								dataType: "json",
								url     : rektic.ajaxurl,
								data    : data,
								success : function(response){
									if(response.type === "success"){
										let listOfWebsites = $("<div></div>")
											.addClass("rektic-art-export");

										let elt = $("<p>", {
											text: "Keywords: " + response.keyword,
										});
										listOfWebsites.append(elt);

										let elt2 = $("<p>", {
											text: "Country: " + response.country,
										});
										listOfWebsites.append(elt2);

										let elt3 = $("<p>", {
											text: "Language: " + response.lang,
										});
										listOfWebsites.append(elt3);

										let elt4 = $("<p>", {
											text: "Description: " + response.metaDescription,
										});
										listOfWebsites.append(elt4);

										const opts = {
											info  : listOfWebsites[0].outerHTML,
											title : "Export article",
											ok    : () => {
												exportArticle(id);
											},
											no    : () => {
											},
											okText: "Export",
											noText: "Cancel",
										};

										cxDialog(opts);

									}
									else{
										showError();
									}
								},
								complete: () => {
									cxDialog.close();
								},
							});

			});

		let exportArticle = (id) => {

			let data = {
				nonce : rekticArtList.nonce,
				action: "rektic_export_article",
				id    : id,
			};

			cxDialog(loaderAlert);

			jQuery.ajax({
							type    : "post",
							dataType: "json",
							url     : rektic.ajaxurl,
							data    : data,
							success : function(response){
								if(response.type === "success"){
									window.location.href = response.link;
								}
								else{
									cxDialog.close();
									showError();
								}
							},
							complete: () => {
							},
						});

		};
	});
