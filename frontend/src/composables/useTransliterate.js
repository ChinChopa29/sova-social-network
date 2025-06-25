
export const useTransliterate = () => {
  const transliterate = (text) => {
    const ru = {
      а: "a", б: "b", в: "v", г: "g", д: "d", е: "e", ё: "e", ж: "zh", з: "z",
      и: "i", й: "y", к: "k", л: "l", м: "m", н: "n", о: "o", п: "p", р: "r",
      с: "s", т: "t", у: "u", ф: "f", х: "h", ц: "c", ч: "ch", ш: "sh",
      щ: "shch", ы: "y", э: "e", ю: "yu", я: "ya", " ": "-", _: "-", ",": "",
    };

    return text
      .toLowerCase()
      .split("")
      .map((char) => ru[char] || char)
      .join("")
      .replace(/[^a-z0-9-]/g, "")
      .replace(/-+/g, "-")
      .replace(/^-|-$/g, "");
  };

  return { transliterate };
};
