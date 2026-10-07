const fs = require("node:fs/promises");
const path = require("node:path");
const { createRequire } = require("node:module");

const browserTools = process.env.TOUCHGRASS_VIDEO_TOOLS;
const edgePath = process.env.TOUCHGRASS_EDGE_PATH;
const frameDirectory = process.env.TOUCHGRASS_FRAME_DIRECTORY;
const baseUrl = process.env.TOUCHGRASS_APP_URL || "http://localhost:8081/";

if (!browserTools || !edgePath || !frameDirectory) {
  throw new Error("Set TOUCHGRASS_VIDEO_TOOLS, TOUCHGRASS_EDGE_PATH, and TOUCHGRASS_FRAME_DIRECTORY.");
}

const requireFromTools = createRequire(path.join(browserTools, "package.json"));
const { chromium } = requireFromTools("playwright-core");
const fps = 4;
const frameCount = fps * 60;
const captions = [
  "This is TouchGrass AI.",
  "Create a 45-minute birding mission.",
  "Now I disconnect the internet.",
  "Take a bird photo.",
  "Local Gemma analyzes it.",
  "Save the observation and put the phone away.",
];

const sleepUntil = async (target, start) => {
  const delay = target - (Date.now() - start);
  if (delay > 0) await new Promise((resolve) => setTimeout(resolve, delay));
};

async function main() {
  await fs.mkdir(frameDirectory, { recursive: true });
  const browser = await chromium.launch({
    executablePath: edgePath,
    headless: true,
    args: ["--disable-gpu", "--no-first-run", "--no-default-browser-check"],
  });

  let context;
  let page;
  let missionId;
  let observationId;
  let captureError;
  const browserErrors = [];

  try {
    context = await browser.newContext({
      viewport: { width: 1280, height: 720 },
      deviceScaleFactor: 1,
      serviceWorkers: "allow",
    });
    await context.addInitScript(() => {
      Object.defineProperty(window, "speechSynthesis", {
        configurable: true,
        value: { cancel() {}, speak() {} },
      });
    });
    page = await context.newPage();
    page.setDefaultTimeout(12000);
    page.on("pageerror", (error) => browserErrors.push(error.message));
    page.on("requestfailed", (request) =>
      browserErrors.push(`${request.method()} ${request.url()}: ${request.failure()?.errorText}`),
    );
    await page.goto(baseUrl, { waitUntil: "domcontentloaded" });
    await page.locator("#outingForm").waitFor({ state: "visible" });
    await page.evaluate(() => document.fonts.ready);

    await page.evaluate(() => {
      const style = document.createElement("style");
      style.textContent = `
        #recording-caption {
          position: fixed; z-index: 2147483647; left: 50%; bottom: 18px;
          transform: translateX(-50%); max-width: 90vw; padding: 12px 22px;
          border: 1px solid rgba(255,255,255,.22); border-radius: 14px;
          color: white; background: rgba(12,24,17,.92);
          font: 600 22px/1.35 system-ui, sans-serif; text-align: center;
          box-shadow: 0 8px 32px rgba(0,0,0,.25); pointer-events: none;
        }
        #recording-concept {
          position: fixed; z-index: 2147483646; top: 26px; right: 24px;
          width: 340px; padding: 18px 20px; border-radius: 16px;
          border: 1px solid #b5894c; color: #fff8ea;
          background: rgba(29,34,27,.97); box-shadow: 0 12px 32px #0005;
          font: 15px/1.45 system-ui, sans-serif; pointer-events: none;
        }
        #recording-concept strong {
          display: block; margin-bottom: 7px; color: #f2c47e;
          font-size: 13px; letter-spacing: .08em; text-transform: uppercase;
        }
      `;
      document.head.append(style);
      const caption = document.createElement("div");
      caption.id = "recording-caption";
      caption.textContent = "This is TouchGrass AI.";
      document.body.append(caption);
    });

    const startedAt = Date.now();
    const capture = (async () => {
      for (let index = 0; index < frameCount; index++) {
        await sleepUntil(Math.round((index * 1000) / fps), startedAt);
        if (captureError) break;
        const name = `frame-${String(index).padStart(4, "0")}.png`;
        try {
          await page.screenshot({
            path: path.join(frameDirectory, name),
            animations: "allow",
            caret: "hide",
            timeout: 20000,
          });
        } catch (error) {
          const previousFrame = path.join(frameDirectory, `frame-${String(index - 1).padStart(4, "0")}.png`);
          if (index > 0 && (await fs.stat(previousFrame).catch(() => null))) {
            await fs.copyFile(previousFrame, path.join(frameDirectory, name));
            console.warn(`Frame ${index}: reused the previous browser frame after a capture delay.`);
          } else {
            captureError = error;
          }
          if (captureError) break;
        }
      }
    })();

    const setCaption = (index) =>
      page.locator("#recording-caption").evaluate((node, text) => {
        node.textContent = text;
      }, captions[index]);

    const conceptCard = async (title, description) => {
      await page.evaluate(
        ({ title, description }) => {
          let card = document.querySelector("#recording-concept");
          if (!card) {
            card = document.createElement("aside");
            card.id = "recording-concept";
            document.body.append(card);
          }
          card.replaceChildren();
          const heading = document.createElement("strong");
          heading.textContent = title;
          const body = document.createElement("div");
          body.textContent = description;
          card.append(heading, body);
        },
        { title, description },
      );
    };

    await sleepUntil(10000, startedAt);
    await setCaption(1);
    await page.locator("#plan").scrollIntoViewIfNeeded();
    await page.locator('#outingForm [name="activity"]').selectOption("walk");
    await page.locator('#outingForm [name="location"]').fill("Riverside Park");
    await page.locator('#outingForm [name="duration"]').fill("45");
    await page.locator('#outingForm [name="difficulty"]').selectOption("easy");
    await page
      .locator('#outingForm [name="notes"]')
      .fill("Birding walk: observe quietly from the trail, note field marks and habitat, and take a water break.");

    const missionSave = await page.evaluate(async () => {
      const form = document.querySelector("#outingForm");
      const values = Object.fromEntries(new FormData(form).entries());
      const response = await fetch("/api/outings", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          activity: values.activity,
          title: "45-minute birding mission",
          location: values.location,
          duration: values.duration,
          difficulty: values.difficulty,
          notes: values.notes,
          ai_plan: "",
        }),
      });
      const result = await response.json();
      if (!response.ok || !result.ok) throw new Error("The real mission save endpoint rejected the demo entry.");
      return result;
    });
    missionId = missionSave.id;
    await page.locator("#outingResult").evaluate((node, id) => {
      node.classList.remove("hidden");
      node.replaceChildren();
      const saved = document.createElement("strong");
      saved.textContent = `45-minute birding mission saved locally (#${id}).`;
      const note = document.createElement("p");
      note.textContent = "Mission data is stored in this app's local SQLite database. AI planning is not run in this recording.";
      node.append(saved, note);
    }, missionId);

    await sleepUntil(20000, startedAt);
    await setCaption(2);
    await context.setOffline(true);
    await page.evaluate(() => {
      const label = document.createElement("aside");
      label.id = "recording-concept";
      label.innerHTML = "";
      const heading = document.createElement("strong");
      heading.textContent = "Browser offline preview";
      const body = document.createElement("div");
      body.textContent = "Only this recording tab is disconnected. The host and app services remain online.";
      label.append(heading, body);
      document.body.append(label);
    });
    await page.waitForFunction(() => navigator.onLine === false);

    await sleepUntil(30000, startedAt);
    await setCaption(3);
    await page.locator("#birdingForm").scrollIntoViewIfNeeded();
    await conceptCard(
      "Photo capture · concept only",
      "The current TouchGrass AI site has no bird-photo capture or upload feature. This preview is not a real camera capture.",
    );

    await sleepUntil(40000, startedAt);
    await setCaption(4);
    await conceptCard(
      "Local Gemma photo analysis · concept only",
      "This is a visual mockup, not an app result. The current site does not send bird photos to Gemma or analyze images.",
    );

    await sleepUntil(50000, startedAt);
    await context.setOffline(false);
    await page.waitForFunction(() => navigator.onLine === true);
    await page.evaluate(() => document.querySelector("#recording-concept")?.remove());
    await page.locator("#logForm").scrollIntoViewIfNeeded();
    await page.locator('#logForm [name="kind"]').selectOption("bird");
    await page.locator('#logForm [name="name"]').fill("Unknown bird");
    await page.locator('#logForm [name="location"]').fill("Riverside Park");
    await page
      .locator('#logForm [name="notes"]')
      .fill("Observed beside the river trail; quiet birding stop during the 45-minute mission.");
    const saveResponse = page.waitForResponse(
      (response) => response.url().includes("/api/logs") && response.request().method() === "POST",
      { timeout: 4000 },
    );
    await page.locator('#logForm button[type="submit"]').click();
    const response = await saveResponse.catch(() => null);
    if (response) {
      const logSave = await response.json();
      if (!response.ok() || !logSave.ok) throw new Error("The real field-observation save endpoint failed.");
      observationId = logSave.id;
    } else if ((await page.locator("#logResult").innerText()).includes("Observation saved locally.")) {
      observationId = "saved-by-form";
    } else {
      const logSave = await page.evaluate(async () => {
        const form = document.querySelector("#logForm");
        const response = await fetch("/api/logs", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify(Object.fromEntries(new FormData(form).entries())),
        });
        const result = await response.json();
        if (!response.ok || !result.ok) throw new Error("The real field-observation save endpoint failed.");
        return result;
      });
      observationId = logSave.id;
      await page.locator("#logResult").evaluate((node, id) => {
        node.classList.remove("hidden");
        node.textContent = `Observation saved locally (#${id}).`;
      }, observationId);
    }
    await page.getByText(/Observation saved locally/i).waitFor({ state: "visible" });

    await capture;
    if (captureError) throw captureError;
    await page.waitForFunction(() => navigator.onLine === true);

    console.log(
      JSON.stringify({
        page: page.url(),
        title: await page.title(),
        frames: frameCount,
        durationSeconds: frameCount / fps,
        missionId,
        observationId,
        savedObservationConfirmation: await page.locator("#logResult").innerText(),
        photoAnalysisIsConceptOnly: true,
        browserErrors,
      }),
    );
  } finally {
    if (context) await context.close();
    await browser.close();
  }
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
