function color(d) {
  if (d === "normal") return "success"
  if (d === "mi") return "error"

  return "primary"
}

function text(d) {
  if (d === "normal") return "Normal"
  if (d === "mi") return "MI" // ganti jadi Myocardial Infarction?

  return "No Data"
}

export default {
  color,
  text,
}
