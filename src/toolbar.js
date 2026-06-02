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

function createToolbar() {
  const toolbar = document.createElement("div");
  toolbar.className =
    "fixed left-2 bottom-2 z-50 hidden rounded-lg bg-black/85 px-3 py-2 text-xs font-bold text-white shadow-lg pointer-events-none";
  toolbar.setAttribute("data-tablo-toolbar", "");
  toolbar.setAttribute("aria-hidden", "true");
  toolbar.title = "Debug toolbar - Shift+G to toggle";
  toolbar.innerHTML = `<div class="flex items-center gap-2"><span class="text-white/60">BP</span><span><span class="sm:hidden">XS</span><span class="hidden sm:inline md:hidden">SM</span><span class="hidden md:inline lg:hidden">MD</span><span class="hidden lg:inline xl:hidden">LG</span><span class="hidden xl:inline 2xl:hidden">XL</span><span class="hidden 2xl:inline">2XL</span></span><span class="h-3 w-px bg-white/20"></span><span class="text-white/60">VW</span><span data-tablo-toolbar-viewport>0 × 0</span></div>`;
  return toolbar;
}

function initializeToolbar() {
  if (document.querySelector(TOOLBAR_SELECTOR)) {
    return;
  }

  const toolbar = createToolbar();
  document.body.appendChild(toolbar);
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
