$(document).ready(function () {

  /* ===== Banner height ===== */
  function setNewsBannerHeight() {
    const header = $(".main-header");
    const headerHeight = header.length > 0 ? header.outerHeight() : 80;
    const viewportHeight = window.innerHeight;
    const bannerHeight = viewportHeight - headerHeight;
    $("#news-banner-section").css("height", bannerHeight + "px");
  }

  setNewsBannerHeight();
  $(window).on("resize", handleResize(setNewsBannerHeight, 250));

  /* ===== News Quick View Modal ===== */
  const $newsModal   = $("#news-quick-view-modal");
  const $modalLoading = $("#news-modal-loading");
  const $modalContent = $("#news-modal-content");

  function openNewsModal() {
    $newsModal.addClass("active");
    $("body").css("overflow", "hidden");
  }

  function closeNewsModal() {
    $newsModal.removeClass("active");
    $("body").css("overflow", "");
    // reset content
    $modalLoading.show();
    $modalContent.hide();
    $("#news-modal-image").attr("src", "");
    $("#news-modal-title").text("");
    $("#news-modal-date").text("");
    $("#news-modal-body").html("");
  }

  // Click "Đọc nhanh"
  $(document).on("click", ".btn-quick-view", function (e) {
    e.preventDefault();
    e.stopPropagation();

    const newsId = $(this).data("news-id");
    if (!newsId) return;

    openNewsModal();

    $.ajax({
      url: getApiPath("news/get.php?id=" + newsId),
      method: "GET",
      dataType: "json",
      success: function (response) {
        $modalLoading.hide();

        if (response.success && response.data) {
          const data = response.data;

          // Image
          let imgSrc = data.image || "assets/img/news/news_one.jpg";
          if (!imgSrc.startsWith("http") && !imgSrc.startsWith("/")) {
            const currentPath = window.location.pathname;
            if (currentPath.includes("/pages/news/")) {
              imgSrc = "../../" + imgSrc;
            } else if (currentPath.includes("/pages/")) {
              imgSrc = "../../../" + imgSrc;
            }
          }
          $("#news-modal-image").attr("src", imgSrc).attr("alt", escapeHtml(data.title));

          // Date & title
          $("#news-modal-date").text(data.date || "");
          $("#news-modal-title").text(data.title || "");

          // Render markdown nếu có marked.js, fallback plain text
          const rawContent = data.content || "";
          if (typeof marked !== "undefined") {
            $("#news-modal-body").html(marked.parse(rawContent));
          } else {
            $("#news-modal-body").text(rawContent);
          }

          // Link "Đọc toàn bộ bài viết"
          const detailUrl = getApiPath("").replace("api/", "") + "pages/news/detail.php?id=" + data.id;
          $("#news-modal-detail-link").attr("href", detailUrl);

          $modalContent.show();
        } else {
          closeNewsModal();
          if (typeof showSnackBar === "function") {
            showSnackBar("failed", response.message || "Không thể tải bài viết.");
          }
        }
      },
      error: function () {
        closeNewsModal();
        if (typeof showSnackBar === "function") {
          showSnackBar("failed", "Có lỗi xảy ra khi tải bài viết. Vui lòng thử lại.");
        }
      }
    });
  });

  // Đóng modal
  $("#close-news-modal-btn").on("click", function (e) {
    e.preventDefault();
    closeNewsModal();
  });

  $newsModal.find(".modal-overlay").on("click", function (e) {
    if (e.target === this) closeNewsModal();
  });

  $(document).on("keydown", function (e) {
    if (e.key === "Escape" && $newsModal.hasClass("active")) {
      closeNewsModal();
    }
  });
});
