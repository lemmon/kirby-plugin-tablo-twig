const TOOLBAR_SELECTOR = "[data-tablo-toolbar]";
const VIEWPORT_SELECTOR = "[data-tablo-toolbar-viewport]";
const STORAGE_KEY = "tabloToolbarVisible";

function getStoredVisibility() {
  try {
    return window.localStorage.getItem(STORAGE_KEY) === "true";
  } catch {
    return false;
  }
}

function storeVisibility(isVisible) {
  try {
    window.localStorage.setItem(STORAGE_KEY, isVisible ? "true" : "false");
  } catch {
    return;
  }
}

function setToolbarVisibility(toolbar, isVisible) {
  toolbar.classList.toggle("hidden", !isVisible);
  toolbar.setAttribute("aria-hidden", String(!isVisible));
  storeVisibility(isVisible);
}

function getViewportDimensions() {
  const viewport = window.visualViewport;

  if (viewport) {
    return {
      width: Math.round(viewport.width),
      height: Math.round(viewport.height),
    };
  }

  return {
    width: window.innerWidth,
    height: window.innerHeight,
  };
}

function updateViewport(toolbar) {
  const viewport = toolbar.querySelector(VIEWPORT_SELECTOR);

  if (!viewport) {
    return;
  }

  const dimensions = getViewportDimensions();
  viewport.textContent = `${dimensions.width} × ${dimensions.height}`;
}

function initializeToolbar() {
  const toolbar = document.querySelector(TOOLBAR_SELECTOR);

  if (!toolbar) {
    return;
  }

  setToolbarVisibility(toolbar, getStoredVisibility());
  updateViewport(toolbar);

  document.addEventListener("keydown", (event) => {
    if (!event.shiftKey || event.key.toLowerCase() !== "g") {
      return;
    }

    if (
      event.target instanceof HTMLElement &&
      (event.target.isContentEditable ||
        ["INPUT", "TEXTAREA", "SELECT"].includes(event.target.tagName))
    ) {
      return;
    }

    event.preventDefault();
    setToolbarVisibility(toolbar, toolbar.classList.contains("hidden"));
  });

  const viewport = window.visualViewport;

  if (viewport) {
    viewport.addEventListener("resize", () => updateViewport(toolbar));
    viewport.addEventListener("scroll", () => updateViewport(toolbar));
  } else {
    window.addEventListener("resize", () => updateViewport(toolbar));
  }
}

if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initializeToolbar, {
    once: true,
  });
} else {
  initializeToolbar();
}
