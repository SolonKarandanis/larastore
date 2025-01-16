import {FC} from 'react'

interface Props{
  amount:number;
  currency?:string;
  locale?:string;
}
const CurrencyFormatter:FC<Props>= ({amount,currency='USD',locale}) => {

  return new Intl.NumberFormat(locale,{
    style:'currency',
    currency
  }).format(amount);
}
export default CurrencyFormatter
