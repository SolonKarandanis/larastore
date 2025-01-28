import {CartItem as Item} from "@/types";
import {FC} from "react";


interface Props{
  item:Item;
}
const CartItem:FC<Props> = ({item}) => {
  return (
    <div>CartItem</div>
  )
}
export default CartItem
