import {FC} from 'react'
import {Product} from "@/types";

interface Props{
  product:Product;
  variationsOptions:number[]
}
const Show:FC<Props> = ({product,variationsOptions}) => {
  return (
    <div>TEst</div>
  )
}
export default Show
