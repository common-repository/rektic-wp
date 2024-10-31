/**
 * Rektic WP
 *
 * This file controls the article creation process
 *
 * @package   rektic-wp
 * @author    SAKHRAOUI Omar <info@omarion.me>
 * @copyright 2022 Rektic WP
 * @license   GPLv3
 * @link      https://omarion.me
 */
let country      = "US";
let articleID    = "";
let waitingForH2 = false,
	h1s          = [],
	selectedH1   = [],
	selectedMeta = [],
	h2s          = [],
	metas        = [],
	loaderAlert  = {
		maskClose: false,
		info     : "<img class=\"rek-loader Animationlogo01\" id=\"rek-loader\" style='margin: 0 auto;display: block;' src=\"" +
				   rektic.interwind + "\" alt=\"\">",
	};
	upgradeAlert  = {
		maskClose: true,
		info     : "<p>Please upgrade your account <a target=\"_blank\" href='https://app.rektic.ai/offre'>here</a> then refresh the editor</p>",
	};

jQuery(document)
	.ready(function($){

		let mkConfig = {
			positionY : "bottom",
			positionX : "right",
			max       : 5,
			scrollable: false,
			duration  : 2000,

		};

		mkNotifications(mkConfig);

		$(".stages label")
			.on("click", (r) => {
				r.preventDefault();
				r.stopPropagation();
			});

		let loader = $("#rek-loader");

		$(".form-edit-rektic .niceCountryInputSelector")
			.each(function(i, e){
				new NiceCountryInput(e).init();
			});
		$(".form-edit-rektic .stages label")
			.on("click", function(){
				let radioButtons  = $(".form-edit-rektic input:radio");
				let selectedIndex = radioButtons.index(radioButtons.filter(":checked"));
				selectedIndex     = selectedIndex + 1;
			});

		$(".form-edit-rektic button")
			.on("click", function(e){
				let radioButtons  = $(".form-edit-rektic input:radio");
				let selectedIndex = radioButtons.index(radioButtons.filter(":checked"));

				selectedIndex = selectedIndex + 2;

				if(selectedIndex === 2){
					if($("#keywords")
						   .val() === ""){
						showError("Some data is missing", "Please add keywords");
						return;
					}
					step1Submit(e);
				}

				if(selectedIndex === 3){
					if(selectedH1 === ""){
						showError("Some data is missing", "Please select H1");
						return;
					}

					step2Submit(e);
				}

				if(selectedIndex === 4){
					exportArticle();
				}
			});

		function moveForewards(step){
			if(step === 3){
				$("button")
					.html("Export");
			}
			$(".form-edit-rektic input[type=\"radio\"]:nth-of-type(" + step + ")")
				.prop("checked", true);
		}

		function step1Submit(e){
			e.preventDefault();
			e.stopPropagation();

			let $keywords = $("#keywords");
			$keywords.removeClass("invalid");
			let data = {
				action  : "rektic_step1",
				nonce   : $("#nonce1")
					.val(),
				keywords: $keywords.val(),
				language: $("#lang")
					.val(),
				country : country,
			};

			if(data.keywords === ""){
				$keywords.addClass("invalid");
				return;
			}

			loader.show();

			jQuery.ajax({
							type    : "post",
							dataType: "json",
							url     : rektic.ajaxurl,
							data    : data,
							success : function(response){
								if(response.type === "success"){
									articleID = response.id;
									prepareStep2();
									moveForewards(2);
								}
								else{
									showError();
								}
							},
							complete: () => {
								loader.hide();
							},
						});
		}

		function step2Submit(e){
			e.preventDefault();
			e.stopPropagation();

			moveForewards(3);
		}

		function prepareStep2(){
			let data = {
				action: "rektic_get_h1s",
				nonce : $("#nonce2")
					.val(),
				id    : articleID,
			};

			jQuery.ajax({
							type    : "post",
							dataType: "json",
							url     : rektic.ajaxurl,
							data    : data,
							success : function(response){
								if(response.type === "success"){
									h1s = response.heads;

									let theList = $("<ul></ul>");
									$.each(h1s, function(index, value){
										index ++;
										let chk   = $("<input type=\"radio\" class=\"h1rek\" name=\"h1rek\" />")
											.attr("id", "select-" + index)
											.val(value)
											.data("h1id", index);
										let label = $(
											"<label class='h1label' id='label-" + index + "' for=\"select-" + index + "\"></label>")
											.text(value)
											.data("h1id", index);
										let elt   = $("<li></li>")
											.addClass("h1select")
											.data("h1id", index);
										elt.append(chk)
										   .append(label);
										let display = $("<div></div>")
											.addClass("h1display")
											.data("h1id", index)
											.attr("id", "h1disp" + index)
											.hide();
										theList.append(elt);
										theList.append(display);
									});
									$("#step2-loader")
										.after(theList);
									startH1Listen();
								}
								else{
									showError();
								}
							},
							complete: () => {
								$("#step2-loader")
									.hide();
							},
						});
		}

		function startH1Listen(){
			$(".h1rek")
				.off("change")
				.on("change", (event) => {
					if(waitingForH2){
						event.preventDefault();
						event.stopImmediatePropagation();
						event.stopPropagation();
						return;
					}
					let $this            = $(event.target),
						id               = $this.data("h1id"),
						displayContainer = $("#h1disp" + id),
						label            = $("#label-" + id);

					$(".h1display")
						.empty()
						.hide();
					displayContainer.show()
									.css("display", "flex");
					prepareH2s(displayContainer, id, label.text());
				});
		}

		function startMetaListen(){
			$(".metarek")
				.off("change")
				.on("change", (event) => {
					if(waitingForH2){
						event.preventDefault();
						event.stopImmediatePropagation();
						event.stopPropagation();
						return;
					}
					let $this = $(event.target),
						id    = $this.data("metaid"),
						label = $("#meta-label-" + id);

					selectedMeta = label.text();

					let prevMeta = $("#preview-meta");
					prevMeta.empty();
					prevMeta.text(selectedMeta);
				});
		}

		function prepareH2s(container, id, h1){
			let data     = {
				action: "rektic_get_h2s",
				nonce : $("#nonce2")
					.val(),
				id    : articleID,
				h1    : h1,
				h1id  : id,
			};
			waitingForH2 = true;
			container.empty();

			let prevH1 = $("#preview-h1");
			prevH1.text(h1);
			selectedH1 = h1;

			container
				.append(
					"<img class=\"step-loader h1loader Animationlogo01\" id=\"h1disp" + id + "-loader\"" + " src=\"" + rektic.interwind +
					"\" alt=''/>");

			jQuery.ajax({
							type    : "post",
							dataType: "json",
							url     : rektic.ajaxurl,
							data    : data,
							success : function(response){
								if(response.type === "success"){
									h2s        = response.heads;
									metas      = response.metas;
									let prevH2 = $("#preview-h2");
									prevH2.empty();
									let prevMeta = $("#preview-meta");
									prevMeta.empty();
									let headsList = $("<ul><strong>H2 List</strong></ul>");
									let metaList  = $("<ul><strong>Meta Description</strong></ul>");
									$.each(h2s, function(index, value){
										let elt = $("<li></li>")
											.addClass("advance")
											.text(value);
										headsList.append(elt);
										prevH2.append(elt.clone());
									});
									$.each(metas, function(index, value){
										index ++;
										let chk   = $("<input type=\"radio\" class=\"metarek\" name=\"metarek\" />")
											.attr("id", "meta-select-" + index)
											.val(value)
											.data("metaid", index);
										let label = $("<label class='meta-label' id='meta-label-" + index + "' for=\"meta-select-" + index +
													  "\"></label>")
											.text(value)
											.data("metaid", index);
										let elt   = $("<li></li>")
											.addClass("advance")
											.data("metaid", index);
										elt.append(chk)
										   .append(label);

										metaList.append(elt);
									});

									$("#h1disp" + id + "-loader")
										.hide();

									container.append(headsList)
											 .append(metaList);
									startMetaListen();
								}
								else{
									showError();
								}
							},
							complete: () => {
								waitingForH2 = false;
							},
						});
		}

		function exportArticle(){
			let info = {
				h1             : selectedH1,
				h2             : h2s,
				metaDescription: selectedMeta,
			};
			let data = {
				action: "rektic_select_info",
				nonce : $("#nonce3")
					.val(),
				id    : articleID,
				info  : info,
			};
			loader.show();

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
									showError();
								}
							},
							complete: () => {
								loader.hide();
							},
						});
		}
	});

function onChangeCallback(ctr){
	country = ctr;
}

let showError = (title = "Server error", message = "Something went wrong while requesting the data. Please refresh the page and retry.") => {

	console.trace();
	mkNoti(title, message, {
		status: "danger",
	});
};
