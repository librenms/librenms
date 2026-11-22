/**
 * x-tooltip="expression" shows a bootstrap tooltip with the evaluated content, empty content shows nothing.
 * Modifiers: .html to render html content, .click to also toggle on click, .nowrap to keep content on one line
 */
export default function tooltip(Alpine) {
    Alpine.directive("tooltip", (el, { expression, modifiers }, { evaluateLater, effect, cleanup }) => {
        const getContent = evaluateLater(expression);
        const $el = $(el);
        let content = "";

        $el.tooltip({
            title: () => content,
            html: modifiers.includes("html"),
            trigger: modifiers.includes("click") ? "hover click" : "hover",
            container: "body",
            template: modifiers.includes("nowrap")
                ? '<div class="tooltip" role="tooltip"><div class="tooltip-arrow"></div><div class="tooltip-inner tw:max-w-none tw:whitespace-nowrap"></div></div>'
                : undefined,
        });

        effect(() =>
            getContent((value) => {
                content = value || "";
                if (!content) {
                    $el.tooltip("hide");
                }
            }),
        );

        cleanup(() => $el.tooltip("destroy"));
    });
}
